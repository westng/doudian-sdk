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
use DouDianSdk\Core\Http\HttpClientFactory;
use DouDianSdk\Core\Swoole\PoolConfig;
use DouDianSdk\Core\Swoole\RuntimeDetector;
use DouDianSdk\Core\Token\AccessToken;
use DouDianSdk\Core\Token\AccessTokenBuilder;

/**
 * 抖店SDK主入口类.
 */
class DouDianSdk
{
    /**
     * SDK版本号
     */
    const VERSION = '2.1.2';

    /**
     * @var GlobalConfig 全局配置
     */
    private $config;

    /**
     * @var DouDianOpClient API客户端
     */
    private $client;

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

        // 应用选项配置（需要在创建客户端之前）
        $this->applyOptions($options);

        // 创建客户端
        $this->client = DouDianOpClient::getInstance();

        // 设置基本配置
        if (!empty($appKey) && !empty($appSecret)) {
            $this->config->setCredentials($appKey, $appSecret);
        }
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

        // 连接池配置（Swoole 环境有效）
        if (isset($options['pool'])) {
            $poolConfig = PoolConfig::fromArray($options['pool']);
            $poolConfig->validate();
            
            $this->config->setPoolConfig(
                $poolConfig->maxConnections,
                $poolConfig->maxIdleTime,
                $poolConfig->waitTimeout
            );

            // 设置到 DouDianOpClient
            DouDianOpClient::setPoolConfig($poolConfig);
            
            // 配置 HttpClientFactory
            HttpClientFactory::configure([], $poolConfig);
        }
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
     * 设置调试模式.
     *
     * @param bool $debug 是否启用调试模式
     */
    public function setDebug($debug): self
    {
        $this->config->setDebug($debug);

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
     * 获取SDK版本号
     *
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * 设置连接池配置（Swoole 环境有效）
     *
     * @param int $maxConnections 最大连接数
     * @param int $maxIdleTime 最大空闲时间（秒）
     * @param float $waitTimeout 等待超时（秒）
     * @return self
     */
    public function setPoolConfig($maxConnections = 50, $maxIdleTime = 60, $waitTimeout = 3.0): self
    {
        $this->config->setPoolConfig($maxConnections, $maxIdleTime, $waitTimeout);

        $poolConfig = PoolConfig::fromArray([
            'max_connections' => $maxConnections,
            'max_idle_time' => $maxIdleTime,
            'wait_timeout' => $waitTimeout,
        ]);

        DouDianOpClient::setPoolConfig($poolConfig);
        HttpClientFactory::configure([], $poolConfig);

        return $this;
    }

    /**
     * 获取连接池统计信息
     *
     * @return array
     */
    public function getPoolStats(): array
    {
        return HttpClientFactory::getPoolStats();
    }

    /**
     * 获取当前运行环境
     *
     * @return string 'swoole-coroutine', 'swoole-sync', 或 'fpm'
     */
    public function getEnvironment(): string
    {
        return HttpClientFactory::getEnvironment();
    }

    /**
     * 检查是否在 Swoole 协程环境
     *
     * @return bool
     */
    public function isSwooleCoroutine(): bool
    {
        return RuntimeDetector::inCoroutine();
    }

    /**
     * 关闭所有连接并释放资源
     * 
     * 在长时间运行的进程中（如队列消费者），建议在适当时机调用此方法
     */
    public function shutdown(): void
    {
        HttpClientFactory::shutdown();
    }
}
