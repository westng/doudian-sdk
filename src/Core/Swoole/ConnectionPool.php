<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Core\Swoole;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

/**
 * Swoole 连接池
 * 
 * Worker 级别共享的 HTTP 客户端连接池，避免每个协程创建独立连接
 */
class ConnectionPool
{
    /**
     * @var self|null 单例实例（Worker 级别）
     */
    private static $instance;

    /**
     * @var \SplQueue 空闲客户端队列
     */
    private $idle;

    /**
     * @var int 当前活跃连接数
     */
    private $activeCount = 0;

    /**
     * @var int 总创建连接数
     */
    private $totalCreated = 0;

    /**
     * @var int 总请求数
     */
    private $totalRequests = 0;

    /**
     * @var PoolConfig 连接池配置
     */
    private $config;

    /**
     * @var array Guzzle 客户端配置
     */
    private $clientConfig;

    /**
     * @var \Swoole\Coroutine\Channel|null 等待通道（Swoole 环境）
     */
    private $waitChannel;

    /**
     * @var bool 是否已关闭
     */
    private $closed = false;

    /**
     * 私有构造函数
     *
     * @param PoolConfig $config 连接池配置
     * @param array $clientConfig Guzzle 客户端配置
     */
    private function __construct(PoolConfig $config, array $clientConfig = [])
    {
        $this->config = $config;
        $this->clientConfig = $clientConfig;
        $this->idle = new \SplQueue();

        // 如果在 Swoole 环境，创建等待通道
        if (RuntimeDetector::inCoroutine() && class_exists('\Swoole\Coroutine\Channel')) {
            $this->waitChannel = new \Swoole\Coroutine\Channel($config->maxConnections);
        }
    }

    /**
     * 获取连接池实例（Worker 级别单例）
     *
     * @param PoolConfig|null $config 连接池配置
     * @param array $clientConfig Guzzle 客户端配置
     * @return self
     */
    public static function getInstance(?PoolConfig $config = null, array $clientConfig = []): self
    {
        if (self::$instance === null) {
            self::$instance = new self(
                $config ?? new PoolConfig(),
                $clientConfig
            );
        }

        return self::$instance;
    }

    /**
     * 重置连接池（主要用于测试）
     */
    public static function reset(): void
    {
        if (self::$instance !== null) {
            self::$instance->close();
            self::$instance = null;
        }
    }

    /**
     * 从池中获取客户端
     *
     * @return Client
     * @throws \RuntimeException
     */
    public function get(): Client
    {
        if ($this->closed) {
            throw new \RuntimeException('Connection pool is closed');
        }

        $this->totalRequests++;

        // 1. 尝试从空闲队列获取
        if (!$this->idle->isEmpty()) {
            $this->activeCount++;
            return $this->idle->dequeue();
        }

        // 2. 如果未达到最大连接数，创建新连接
        if ($this->totalCreated < $this->config->maxConnections) {
            return $this->createClient();
        }

        // 3. 等待可用连接
        return $this->waitForClient();
    }

    /**
     * 归还客户端到池中
     *
     * @param Client $client Guzzle 客户端
     */
    public function put(Client $client): void
    {
        if ($this->closed) {
            // 池已关闭，直接丢弃
            $this->activeCount--;
            return;
        }

        $this->activeCount--;

        // 如果有协程在等待，直接唤醒
        if ($this->waitChannel !== null && $this->waitChannel->stats()['consumer_num'] > 0) {
            $this->waitChannel->push($client, 0.001);
            return;
        }

        // 否则放回空闲队列
        $this->idle->enqueue($client);
    }

    /**
     * 创建新的 Guzzle 客户端
     *
     * @return Client
     */
    private function createClient(): Client
    {
        $this->totalCreated++;
        $this->activeCount++;

        $config = array_merge([
            'timeout'         => 30,
            'connect_timeout' => 10,
            'verify'          => false,
            'http_errors'     => false,
            'headers'         => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept'       => 'application/json',
                'User-Agent'   => 'DouDianSDK-PHP/2.1.0-Swoole',
                'from'         => 'sdk',
                'sdk-type'     => 'php',
            ],
        ], $this->clientConfig);

        // 尝试使用协程 Handler
        $handler = $this->createHandler();
        if ($handler !== null) {
            $config['handler'] = $handler;
        }

        return new Client($config);
    }

    /**
     * 创建协程 Handler
     * 
     * 注意：Hyperf PoolHandler 在 SwooleHttpClient 层处理
     * 这里只处理 CoroutineHandler（协程支持，无连接池）
     *
     * @return HandlerStack|null
     */
    private function createHandler(): ?HandlerStack
    {
        // 使用 Hyperf CoroutineHandler（仅协程支持，连接池由 SDK 管理）
        if (class_exists('\Hyperf\Guzzle\CoroutineHandler')) {
            return HandlerStack::create(new \Hyperf\Guzzle\CoroutineHandler());
        }

        // 其次使用 swlib/saber
        if (class_exists('\Swlib\Saber\Handler')) {
            return HandlerStack::create(new \Swlib\Saber\Handler());
        }

        return null;
    }

    /**
     * 等待可用连接
     *
     * @return Client
     * @throws \RuntimeException
     */
    private function waitForClient(): Client
    {
        // Swoole 环境使用 Channel 等待
        if ($this->waitChannel !== null) {
            $client = $this->waitChannel->pop($this->config->waitTimeout);
            
            if ($client === false) {
                throw new \RuntimeException(
                    "Connection pool exhausted: max={$this->config->maxConnections}, " .
                    "active={$this->activeCount}, timeout={$this->config->waitTimeout}s"
                );
            }

            $this->activeCount++;
            return $client;
        }

        // 非 Swoole 环境，直接抛出异常
        throw new \RuntimeException(
            "Connection pool exhausted: max={$this->config->maxConnections}, active={$this->activeCount}"
        );
    }

    /**
     * 关闭连接池
     */
    public function close(): void
    {
        $this->closed = true;

        // 清空空闲队列
        while (!$this->idle->isEmpty()) {
            $this->idle->dequeue();
        }

        // 关闭等待通道
        if ($this->waitChannel !== null) {
            $this->waitChannel->close();
            $this->waitChannel = null;
        }

        $this->activeCount = 0;
        $this->totalCreated = 0;
    }

    /**
     * 获取连接池统计信息
     *
     * @return array
     */
    public function getStats(): array
    {
        return [
            'pool_size'          => $this->config->maxConnections,
            'total_created'      => $this->totalCreated,
            'active_connections' => $this->activeCount,
            'idle_connections'   => $this->idle->count(),
            'wait_queue_size'    => $this->waitChannel ? $this->waitChannel->stats()['consumer_num'] : 0,
            'total_requests'     => $this->totalRequests,
            'closed'             => $this->closed,
        ];
    }

    /**
     * 获取配置
     *
     * @return PoolConfig
     */
    public function getConfig(): PoolConfig
    {
        return $this->config;
    }
}
