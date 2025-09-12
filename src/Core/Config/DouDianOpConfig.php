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

use DouDianSdk\Core\Logger\LoggerInterface;
use DouDianSdk\Core\Logger\NullLogger;

class DouDianOpConfig
{
    /**
     * @var string 应用Key
     */
    public $appKey;

    /**
     * @var string 应用密钥
     */
    public $appSecret;

    /**
     * @var int HTTP连接超时时间（毫秒）
     */
    public $httpConnectTimeout = 1000;

    /**
     * @var int HTTP读取超时时间（毫秒）
     */
    public $httpReadTimeout = 5000;

    /**
     * @var string 开放平台请求URL
     */
    public $openRequestUrl = 'https://openapi-fxg.jinritemai.com';

    /**
     * @var LoggerInterface 日志记录器
     */
    public $logger;

    /**
     * @var bool 是否启用调试模式
     */
    public $debug = false;

    /**
     * @var string 签名方法
     */
    public $signMethod = 'hmac-sha256';

    /**
     * @var int API版本
     */
    public $apiVersion = 2;

    /**
     * @var bool 是否启用重试机制
     */
    public $enableRetry = true;

    /**
     * @var int 最大重试次数
     */
    public $maxRetryTimes = 3;

    /**
     * @var int 重试间隔（毫秒）
     */
    public $retryInterval = 1000;

    /**
     * 构造函数.
     */
    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    /**
     * 设置日志记录器.
     *
     * @param LoggerInterface $logger 日志记录器
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * 设置应用凭证
     *
     * @param string $appKey 应用Key
     * @param string $appSecret 应用密钥
     */
    public function setCredentials($appKey, $appSecret): self
    {
        $this->appKey    = $appKey;
        $this->appSecret = $appSecret;

        return $this;
    }

    /**
     * 设置超时时间.
     *
     * @param int $connectTimeout 连接超时时间（毫秒）
     * @param int $readTimeout 读取超时时间（毫秒）
     */
    public function setTimeout($connectTimeout, $readTimeout): self
    {
        $this->httpConnectTimeout = $connectTimeout;
        $this->httpReadTimeout    = $readTimeout;

        return $this;
    }

    /**
     * 设置调试模式.
     *
     * @param bool $debug 是否启用调试模式
     */
    public function setDebug($debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * 设置重试配置.
     *
     * @param bool $enableRetry 是否启用重试
     * @param int $maxRetryTimes 最大重试次数
     * @param int $retryInterval 重试间隔（毫秒）
     */
    public function setRetryConfig($enableRetry, $maxRetryTimes = 3, $retryInterval = 1000): self
    {
        $this->enableRetry   = $enableRetry;
        $this->maxRetryTimes = $maxRetryTimes;
        $this->retryInterval = $retryInterval;

        return $this;
    }

    /**
     * 验证配置.
     *
     * @throws \InvalidArgumentException
     */
    public function validate(): bool
    {
        if (empty($this->appKey)) {
            throw new \InvalidArgumentException('AppKey is required');
        }

        if (empty($this->appSecret)) {
            throw new \InvalidArgumentException('AppSecret is required');
        }

        if (empty($this->openRequestUrl)) {
            throw new \InvalidArgumentException('OpenRequestUrl is required');
        }

        if ($this->httpConnectTimeout <= 0) {
            throw new \InvalidArgumentException('HttpConnectTimeout must be greater than 0');
        }

        if ($this->httpReadTimeout <= 0) {
            throw new \InvalidArgumentException('HttpReadTimeout must be greater than 0');
        }

        return true;
    }
}
