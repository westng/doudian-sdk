<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Core;

/**
 * 单例模式 Trait.
 *
 * 提供统一的单例模式实现
 */
trait SingletonTrait
{
    /**
     * @var static 单例实例
     */
    private static $instance;

    /**
     * 获取单例实例.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof static)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * 设置单例实例（主要用于测试）.
     *
     * @param static $instance 实例
     */
    public static function setInstance($instance): void
    {
        self::$instance = $instance;
    }

    /**
     * 重置单例实例（主要用于测试）.
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    /**
     * 私有构造函数（防止外部实例化）.
     */
    private function __construct()
    {
        // 子类可以重写此方法
    }

    /**
     * 防止克隆.
     */
    private function __clone()
    {
        // 防止克隆
    }

    /**
     * 防止反序列化.
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton');
    }
}
