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
 * 协程上下文管理器
 * 
 * 用于在 Swoole 协程环境中存储和获取协程级别的数据
 * 
 * 使用示例：
 * ```php
 * // 存储请求追踪 ID
 * CoroutineContext::set('trace_id', uniqid());
 * 
 * // 获取
 * $traceId = CoroutineContext::get('trace_id');
 * ```
 */
class CoroutineContext
{
    /**
     * 获取当前协程的值
     *
     * @param string $key 键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        if (!RuntimeDetector::inCoroutine()) {
            return $default;
        }

        $context = \Swoole\Coroutine::getContext();
        
        if ($context === null) {
            return $default;
        }

        return $context[$key] ?? $default;
    }

    /**
     * 设置当前协程的值
     *
     * @param string $key 键名
     * @param mixed $value 值
     */
    public static function set(string $key, $value): void
    {
        if (!RuntimeDetector::inCoroutine()) {
            return;
        }

        $context = \Swoole\Coroutine::getContext();
        
        if ($context !== null) {
            $context[$key] = $value;
        }
    }

    /**
     * 检查键是否存在
     *
     * @param string $key 键名
     * @return bool
     */
    public static function has(string $key): bool
    {
        if (!RuntimeDetector::inCoroutine()) {
            return false;
        }

        $context = \Swoole\Coroutine::getContext();
        
        if ($context === null) {
            return false;
        }

        return isset($context[$key]);
    }

    /**
     * 删除键
     *
     * @param string $key 键名
     */
    public static function delete(string $key): void
    {
        if (!RuntimeDetector::inCoroutine()) {
            return;
        }

        $context = \Swoole\Coroutine::getContext();
        
        if ($context !== null) {
            unset($context[$key]);
        }
    }
}
