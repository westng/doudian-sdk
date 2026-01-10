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

/**
 * 运行时环境检测器
 * 
 * 用于检测当前是否在 Swoole 协程环境中运行
 */
class RuntimeDetector
{
    /**
     * 检测是否在 Swoole 协程环境中
     *
     * @return bool
     */
    public static function inCoroutine(): bool
    {
        return self::swooleLoaded() && self::getCoroutineId() > 0;
    }

    /**
     * 检测 Swoole 扩展是否已加载
     *
     * @return bool
     */
    public static function swooleLoaded(): bool
    {
        return extension_loaded('swoole');
    }

    /**
     * 获取当前协程 ID
     * 
     * @return int 协程 ID，非协程环境返回 -1
     */
    public static function getCoroutineId(): int
    {
        if (!self::swooleLoaded()) {
            return -1;
        }

        if (!class_exists('\Swoole\Coroutine')) {
            return -1;
        }

        $cid = \Swoole\Coroutine::getCid();
        
        return $cid !== false ? (int) $cid : -1;
    }

    /**
     * 检测是否支持协程上下文
     *
     * @return bool
     */
    public static function supportsContext(): bool
    {
        return self::swooleLoaded() && class_exists('\Swoole\Coroutine\Context');
    }
}
