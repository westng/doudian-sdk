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
 * 文件日志记录器.
 */
class FileLogger implements LoggerInterface
{
    /**
     * @var string 日志文件路径
     */
    private $logFile;

    /**
     * @var int 日志级别
     */
    private $logLevel;

    /**
     * 日志级别常量.
     */
    public const DEBUG    = 100;
    public const INFO     = 200;
    public const WARNING  = 300;
    public const ERROR    = 400;
    public const CRITICAL = 500;

    /**
     * 级别名称映射.
     */
    private $levelNames = [
        self::DEBUG    => 'DEBUG',
        self::INFO     => 'INFO',
        self::WARNING  => 'WARNING',
        self::ERROR    => 'ERROR',
        self::CRITICAL => 'CRITICAL',
    ];

    /**
     * 构造函数.
     *
     * @param string $logFile 日志文件路径
     * @param int $logLevel 日志级别
     */
    public function __construct($logFile = '', $logLevel = self::INFO)
    {
        $this->logLevel = $logLevel;

        if (empty($logFile)) {
            $this->logFile = sys_get_temp_dir() . '/doudian-sdk.log';
        } else {
            $this->logFile = $logFile;
        }

        // 确保日志目录存在
        $logDir = dirname($this->logFile);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * 记录调试信息.
     */
    public function debug($message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * 记录一般信息.
     */
    public function info($message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * 记录警告信息.
     */
    public function warning($message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * 记录错误信息.
     */
    public function error($message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * 记录致命错误.
     */
    public function critical($message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * 记录日志.
     *
     * @param int $level 日志级别
     * @param string $message 日志消息
     * @param array $context 上下文信息
     */
    private function log($level, $message, array $context = []): void
    {
        if ($level < $this->logLevel) {
            return;
        }

        $timestamp  = date('Y-m-d H:i:s');
        $levelName  = $this->levelNames[$level];
        $contextStr = empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);

        $logEntry = "[{$timestamp}] {$levelName}: {$message}{$contextStr}" . PHP_EOL;

        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
