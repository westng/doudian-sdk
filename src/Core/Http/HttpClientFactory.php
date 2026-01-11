<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Core\Http;

use DouDianSdk\Core\Swoole\ConnectionPool;
use DouDianSdk\Core\Swoole\PoolConfig;
use DouDianSdk\Core\Swoole\RuntimeDetector;

/**
 * HTTP 客户端工厂
 * 
 * 根据运行环境自动创建合适的 HTTP 客户端
 * 
 * 关键设计：
 * - FPM 环境：使用进程级单例
 * - Swoole 环境：使用 Worker 级别共享的连接池（不是协程级别）
 *   这样可以避免每个协程创建独立连接导致的 "Too many open files" 问题
 */
class HttpClientFactory
{
    /**
     * @var HttpClientInterface|null FPM 环境下的单例实例
     */
    private static $fpmInstance;

    /**
     * @var SwooleHttpClient|null Swoole 环境下的共享实例（Worker 级别）
     */
    private static $swooleInstance;

    /**
     * @var array Guzzle 配置
     */
    private static $config = [];

    /**
     * @var PoolConfig|null 连接池配置
     */
    private static $poolConfig;

    /**
     * @var array 全局统计
     */
    private static $globalStats = [
        'total_requests' => 0,
        'swoole_clients_created' => 0,
        'fpm_clients_created' => 0,
    ];

    /**
     * 创建 HTTP 客户端（每次创建新实例）
     *
     * @param array $config Guzzle 配置
     * @param PoolConfig|null $poolConfig 连接池配置（仅 Swoole 环境有效）
     * @return HttpClientInterface
     */
    public static function create(array $config = [], ?PoolConfig $poolConfig = null): HttpClientInterface
    {
        if (RuntimeDetector::inCoroutine()) {
            self::$globalStats['swoole_clients_created']++;
            return new SwooleHttpClient($config, $poolConfig);
        }

        self::$globalStats['fpm_clients_created']++;
        return self::createStandardClient($config);
    }

    /**
     * 获取 HTTP 客户端实例
     * 
     * 关键：Swoole 环境下返回 Worker 级别共享的实例，而不是协程级别
     * 这样所有协程共享同一个连接池，避免连接泄漏
     * 
     * 注意：只有在协程环境中才使用 SwooleHttpClient，避免在框架启动早期
     * （如路由注册阶段）触发容器获取导致的问题
     *
     * @param array $config Guzzle 配置
     * @param PoolConfig|null $poolConfig 连接池配置
     * @return HttpClientInterface
     */
    public static function getInstance(array $config = [], ?PoolConfig $poolConfig = null): HttpClientInterface
    {
        // 保存配置供后续使用
        if (!empty($config)) {
            self::$config = $config;
        }
        if ($poolConfig !== null) {
            self::$poolConfig = $poolConfig;
        }

        // 只有在协程环境中才使用 SwooleHttpClient
        // 仅加载 Swoole 扩展但不在协程中时，使用标准 FPM 客户端
        // 这避免了在框架启动早期（路由注册等阶段）触发容器获取
        if (RuntimeDetector::inCoroutine()) {
            return self::getSwooleInstance();
        }

        // FPM 环境或 Swoole 非协程环境：使用进程级单例
        return self::getFpmInstance();
    }

    /**
     * 获取 Swoole 环境的共享实例（Worker 级别）
     *
     * @return SwooleHttpClient
     */
    private static function getSwooleInstance(): SwooleHttpClient
    {
        if (self::$swooleInstance === null) {
            self::$swooleInstance = new SwooleHttpClient(self::$config, self::$poolConfig);
            self::$globalStats['swoole_clients_created']++;
        }

        return self::$swooleInstance;
    }

    /**
     * 获取 FPM 环境的单例实例
     *
     * @return HttpClientInterface
     */
    private static function getFpmInstance(): HttpClientInterface
    {
        if (self::$fpmInstance === null) {
            self::$fpmInstance = self::createStandardClient(self::$config);
            self::$globalStats['fpm_clients_created']++;
        }

        return self::$fpmInstance;
    }

    /**
     * 创建标准 Guzzle 客户端
     *
     * @param array $config Guzzle 配置
     * @return HttpClient
     */
    private static function createStandardClient(array $config): HttpClient
    {
        return HttpClient::createInstance($config);
    }

    /**
     * 设置全局配置
     *
     * @param array $config Guzzle 配置
     * @param PoolConfig|null $poolConfig 连接池配置
     */
    public static function configure(array $config = [], ?PoolConfig $poolConfig = null): void
    {
        self::$config = $config;
        self::$poolConfig = $poolConfig;

        // 如果已有 Swoole 实例，需要重置以应用新配置
        if (self::$swooleInstance !== null) {
            self::$swooleInstance->close();
            self::$swooleInstance = null;
            ConnectionPool::reset();
        }
    }

    /**
     * 关闭所有连接并清理资源
     * 
     * 在长时间运行的进程中（如队列消费者），应该定期调用此方法
     */
    public static function shutdown(): void
    {
        // 清理 Swoole 实例和连接池
        if (self::$swooleInstance !== null) {
            self::$swooleInstance->close();
            self::$swooleInstance = null;
        }

        // 重置连接池
        ConnectionPool::reset();

        // 清理 FPM 单例
        self::$fpmInstance = null;

        // 重置配置
        self::$config = [];
        self::$poolConfig = null;
    }

    /**
     * 重置工厂状态（主要用于测试）
     */
    public static function reset(): void
    {
        self::shutdown();
        self::$globalStats = [
            'total_requests' => 0,
            'swoole_clients_created' => 0,
            'fpm_clients_created' => 0,
        ];
    }

    /**
     * 获取连接池统计信息
     *
     * @return array
     */
    public static function getPoolStats(): array
    {
        $stats = [
            'pool_size'          => self::$poolConfig ? self::$poolConfig->maxConnections : 50,
            'active_connections' => 0,
            'idle_connections'   => 0,
            'wait_queue_size'    => 0,
            'environment'        => self::getEnvironment(),
            'global_stats'       => self::$globalStats,
        ];

        // 从 Swoole 实例获取详细统计
        if (self::$swooleInstance !== null) {
            $clientStats = self::$swooleInstance->getStats();
            $stats = array_merge($stats, $clientStats);
        }

        return $stats;
    }

    /**
     * 检查是否在 Swoole 环境
     *
     * @return bool
     */
    public static function isSwooleEnvironment(): bool
    {
        return RuntimeDetector::inCoroutine() || RuntimeDetector::swooleLoaded();
    }

    /**
     * 获取当前环境名称
     *
     * @return string
     */
    public static function getEnvironment(): string
    {
        if (RuntimeDetector::inCoroutine()) {
            return 'swoole-coroutine';
        }

        if (RuntimeDetector::swooleLoaded()) {
            return 'swoole-sync';
        }

        return 'fpm';
    }
}
