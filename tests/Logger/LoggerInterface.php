<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Tests\Logger;

/**
 * 日志记录器接口.
 */
interface LoggerInterface
{
    /**
     * 记录调试信息.
     *
     * @param string $message 日志消息
     * @param array $context 上下文信息
     */
    public function debug($message, array $context = []): void;

    /**
     * 记录一般信息.
     *
     * @param string $message 日志消息
     * @param array $context 上下文信息
     */
    public function info($message, array $context = []): void;

    /**
     * 记录警告信息.
     *
     * @param string $message 日志消息
     * @param array $context 上下文信息
     */
    public function warning($message, array $context = []): void;

    /**
     * 记录错误信息.
     *
     * @param string $message 日志消息
     * @param array $context 上下文信息
     */
    public function error($message, array $context = []): void;

    /**
     * 记录致命错误.
     *
     * @param string $message 日志消息
     * @param array $context 上下文信息
     */
    public function critical($message, array $context = []): void;
}
