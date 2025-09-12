<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Core\Client;

use DouDianSdk\Core\Config\GlobalConfig;
use DouDianSdk\Core\Exception\DouDianException;
use DouDianSdk\Core\Logger\FileLogger;
use DouDianSdk\Core\Logger\LoggerInterface;
use DouDianSdk\Core\Logger\NullLogger;
use DouDianSdk\Core\Token\AccessTokenBuilder;

/**
 * 抖店SDK主入口类.
 */
class DouDianSdk
{
    /**
     * @var GlobalConfig 全局配置
     */
    private $config;

    /**
     * @var DouDianOpClient API客户端
     */
    private $client;

    /**
     * @var LoggerInterface 日志记录器
     */
    private $logger;

    /**
     * 构造函数.
     *
     * @param string $appKey 应用Key
     * @param string $appSecret 应用密钥
     * @param array $options 配置选项
     */
    public function __construct($appKey = '', $appSecret = '', array $options = [])
    {
        $this->config = GlobalConfig::getGlobalConfig();
        $this->client = DouDianOpClient::getInstance();

        // 设置基本配置
        if (!empty($appKey) && !empty($appSecret)) {
            $this->config->setCredentials($appKey, $appSecret);
        }

        // 应用选项配置
        $this->applyOptions($options);

        // 设置日志记录器
        $this->setupLogger();
    }

    /**
     * 应用配置选项.
     *
     * @param array $options 配置选项
     */
    private function applyOptions(array $options): void
    {
        if (isset($options['debug'])) {
            $this->config->setDebug($options['debug']);
        }

        if (isset($options['timeout'])) {
            $connectTimeout = $options['timeout']['connect'] ?? 1000;
            $readTimeout    = $options['timeout']['read'] ?? 5000;
            $this->config->setTimeout($connectTimeout, $readTimeout);
        }

        if (isset($options['retry'])) {
            $enableRetry = $options['retry']['enable'] ?? true;
            $maxRetries  = $options['retry']['max_times'] ?? 3;
            $interval    = $options['retry']['interval'] ?? 1000;
            $this->config->setRetryConfig($enableRetry, $maxRetries, $interval);
        }

        if (isset($options['open_request_url'])) {
            $this->config->openRequestUrl = $options['open_request_url'];
        }

        if (isset($options['sign_method'])) {
            $this->config->signMethod = $options['sign_method'];
        }

        if (isset($options['api_version'])) {
            $this->config->apiVersion = $options['api_version'];
        }
    }

    /**
     * 设置日志记录器.
     */
    private function setupLogger(): void
    {
        if ($this->config->debug) {
            $logFile      = sys_get_temp_dir() . '/doudian-sdk-' . date('Y-m-d') . '.log';
            $this->logger = new FileLogger($logFile, FileLogger::DEBUG);
        } else {
            $this->logger = new NullLogger();
        }

        $this->config->setLogger($this->logger);
    }

    /**
     * 设置应用凭证
     *
     * @param string $appKey 应用Key
     * @param string $appSecret 应用密钥
     */
    public function setCredentials($appKey, $appSecret): self
    {
        $this->config->setCredentials($appKey, $appSecret);

        return $this;
    }

    /**
     * 设置日志记录器.
     *
     * @param LoggerInterface $logger 日志记录器
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        $this->config->setLogger($logger);

        return $this;
    }

    /**
     * 设置调试模式.
     *
     * @param bool $debug 是否启用调试模式
     */
    public function setDebug($debug): self
    {
        $this->config->setDebug($debug);
        $this->setupLogger(); // 重新设置日志记录器

        return $this;
    }

    /**
     * 获取访问令牌.
     *
     * @param string $codeOrShopId 授权码或店铺ID
     * @param int $type 类型：1-授权码，2-店铺ID
     *
     * @throws DouDianException
     */
    public function getAccessToken($codeOrShopId, $type = ACCESS_TOKEN_CODE): AccessToken
    {
        try {
            return AccessTokenBuilder::build($codeOrShopId, $type);
        } catch (\Exception $e) {
            throw new DouDianException('Failed to get access token: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 刷新访问令牌.
     *
     * @param string $refreshToken 刷新令牌
     *
     * @throws DouDianException
     */
    public function refreshAccessToken($refreshToken): AccessToken
    {
        try {
            return AccessTokenBuilder::refresh($refreshToken);
        } catch (\Exception $e) {
            throw new DouDianException('Failed to refresh access token: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 执行API请求
     *
     * @param object $request 请求对象
     * @param object $param 参数对象
     * @param array $paramData 请求数据数组
     * @param mixed $accessToken 访问令牌
     *
     * @return array 响应结果
     *
     * @throws DouDianException
     */
    public function sendRequest($request, $param, array $paramData, $accessToken): array
    {
        try {
            // 动态设置请求参数，忽略值为 null 的参数
            foreach ($paramData as $key => $value) {
                if (null !== $value) {
                    $param->{$key} = $value;
                }
            }

            if (empty($accessToken)) {
                throw new DouDianException('Access token is required');
            }

            $request->setParam($param);

            // 执行请求并获取响应
            $response = $request->execute($accessToken);

            // 将响应从 stdClass 转换为数组并返回
            return (array) $response;
        } catch (\Exception $e) {
            throw new DouDianException('API request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 便捷的API调用方法.
     *
     * @param string $apiClass API类名（如：'afterSale_List\\AfterSaleListRequest'）
     * @param string $paramClass 参数类名（如：'afterSale_List\\param\\AfterSaleListParam'）
     * @param array $paramData 请求参数
     * @param mixed $accessToken 访问令牌
     *
     * @return array 响应结果
     *
     * @throws DouDianException
     */
    public function callApi($apiClass, $paramClass, array $paramData, $accessToken): array
    {
        try {
            $requestClass   = "\\DouDianSdk\\Api\\{$apiClass}";
            $paramClassFull = "\\DouDianSdk\\Api\\{$paramClass}";

            if (!class_exists($requestClass)) {
                throw new DouDianException("API class not found: {$requestClass}");
            }

            if (!class_exists($paramClassFull)) {
                throw new DouDianException("Param class not found: {$paramClassFull}");
            }

            $request = new $requestClass();
            $param   = new $paramClassFull();

            return $this->sendRequest($request, $param, $paramData, $accessToken);
        } catch (\Exception $e) {
            throw new DouDianException('API call failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 获取配置对象
     */
    public function getConfig(): GlobalConfig
    {
        return $this->config;
    }

    /**
     * 获取日志记录器.
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
