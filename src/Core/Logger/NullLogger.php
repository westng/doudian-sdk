<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Core\Logger;

/**
 * 空日志记录器（不记录任何日志）.
 */
class NullLogger implements LoggerInterface
{
    /**
     * 记录调试信息.
     */
    public function debug($message, array $context = []): void
    {
        // 空实现
    }

    /**
     * 记录一般信息.
     */
    public function info($message, array $context = []): void
    {
        // 空实现
    }

    /**
     * 记录警告信息.
     */
    public function warning($message, array $context = []): void
    {
        // 空实现
    }

    /**
     * 记录错误信息.
     */
    public function error($message, array $context = []): void
    {
        // 空实现
    }

    /**
     * 记录致命错误.
     */
    public function critical($message, array $context = []): void
    {
        // 空实现
    }
}
