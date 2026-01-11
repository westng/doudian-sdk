<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Core\Config;

use DouDianSdk\Core\Swoole\CoroutineContext;
use DouDianSdk\Core\Swoole\RuntimeDetector;

/**
 * 全局配置类.
 *
 * 继承自 DouDianOpConfig，提供单例模式的全局配置管理
 * 支持 Swoole 协程环境下的配置隔离
 */
class GlobalConfig extends DouDianOpConfig
{
    /**
     * @var static|null FPM 环境下的单例实例
     */
    private static $instance;

    /**
     * 协程上下文键
     */
    private const CONTEXT_KEY = 'doudian.global_config';

    /**
     * 获取全局配置实例.
     *
     * @return GlobalConfig
     */
    public static function getGlobalConfig(): GlobalConfig
    {
        return self::getInstance();
    }

    /**
     * 获取单例实例
     * 
     * 注意：只有在真正处于协程环境中才使用协程上下文
     * 避免在框架启动早期触发 Swoole 相关操作
     *
     * @return static
     */
    public static function getInstance(): self
    {
        // 只有在真正处于协程中时才使用协程上下文
        // 这避免了在框架启动早期（路由注册等阶段）触发问题
        if (RuntimeDetector::inCoroutine()) {
            return self::getCoroutineInstance();
        }

        // FPM 环境或 Swoole 非协程环境：使用静态单例
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 获取协程级别的实例
     *
     * @return self
     */
    private static function getCoroutineInstance(): self
    {
        $config = CoroutineContext::get(self::CONTEXT_KEY);

        if ($config === null) {
            $config = new self();
            CoroutineContext::set(self::CONTEXT_KEY, $config);
        }

        return $config;
    }

    /**
     * 重置单例实例（主要用于测试）
     */
    public static function resetInstance(): void
    {
        self::$instance = null;

        if (RuntimeDetector::inCoroutine()) {
            CoroutineContext::delete(self::CONTEXT_KEY);
        }
    }

    /**
     * 设置单例实例（主要用于测试）
     *
     * @param static $instance 实例
     */
    public static function setInstance($instance): void
    {
        if (RuntimeDetector::inCoroutine()) {
            CoroutineContext::set(self::CONTEXT_KEY, $instance);
        } else {
            self::$instance = $instance;
        }
    }
}
