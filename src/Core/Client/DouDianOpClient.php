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

use DouDianSdk\Core\Config\DouDianOpConfig;
use DouDianSdk\Core\Exception\ApiException;
use DouDianSdk\Core\Exception\HttpException;
use DouDianSdk\Core\Http\HttpClient;
use DouDianSdk\Core\Http\HttpRequest;
use DouDianSdk\Core\SingletonTrait;

class DouDianOpClient
{
    use SingletonTrait;

    /**
     * @var HttpClient HTTP客户端
     */
    private $httpClient;

    /**
     * 构造函数.
     */
    private function __construct()
    {
        $this->httpClient = HttpClient::getInstance();
    }

    /**
     * 发送请求
     *
     * @param mixed $request 请求对象
     * @param mixed $accessToken 访问令牌
     *
     * @return mixed 响应结果
     *
     * @throws ApiException|HttpException
     */
    public function request($request, $accessToken)
    {
        $config = $request->getConfig();

        // 验证配置
        try {
            $config->validate();
        } catch (\InvalidArgumentException $e) {
            throw new ApiException('Configuration error: ' . $e->getMessage(), '', '', 1001, $e);
        }

        // 记录请求开始
        $config->logger->info('API request started', [
            'url_path'     => $request->getUrlPath(),
            'access_token' => $this->maskToken($accessToken),
        ]);

        $retryCount = 0;
        $maxRetries = $config->enableRetry ? $config->maxRetryTimes : 0;

        while ($retryCount <= $maxRetries) {
            try {
                $response = $this->executeRequest($request, $accessToken, $config);

                // 检查API响应错误
                $this->checkApiResponse($response, $config);

                $config->logger->info('API request completed successfully', [
                    'url_path'    => $request->getUrlPath(),
                    'retry_count' => $retryCount,
                ]);

                return $response;
            } catch (HttpException $e) {
                ++$retryCount;

                $config->logger->warning('HTTP request failed, retrying', [
                    'error'       => $e->getMessage(),
                    'http_status' => $e->getHttpStatusCode(),
                    'retry_count' => $retryCount,
                    'max_retries' => $maxRetries,
                ]);

                if ($retryCount > $maxRetries) {
                    throw $e;
                }

                // 等待重试间隔
                if ($config->retryInterval > 0) {
                    usleep($config->retryInterval * 1000);
                }
            } catch (ApiException $e) {
                // API错误通常不需要重试
                $config->logger->error('API request failed', [
                    'error'          => $e->getMessage(),
                    'api_error_code' => $e->getApiErrorCode(),
                    'log_id'         => $e->getLogId(),
                ]);

                throw $e;
            }
        }

        throw new HttpException('Max retry attempts exceeded', 0);
    }

    /**
     * 执行HTTP请求
     *
     * @param mixed $request 请求对象
     * @param mixed $accessToken 访问令牌
     * @param DouDianOpConfig $config 配置对象
     *
     * @return mixed 响应结果
     *
     * @throws HttpException
     */
    private function executeRequest($request, $accessToken, DouDianOpConfig $config)
    {
        $urlPath   = $request->getUrlPath();
        $method    = $this->getMethod($urlPath);
        $paramJson = \DouDianSdk\Utils\SignUtil::marshal($request->getParam());
        $appKey    = $config->appKey;
        $appSecret = $config->appSecret;
        $timestamp = time();
        $sign      = \DouDianSdk\Utils\SignUtil::sign($appKey, $appSecret, $method, $timestamp, $paramJson);

        $accessTokenStr = '';

        if (null != $accessToken) {
            $accessTokenStr = is_object($accessToken) ? $accessToken->getAccessToken() : $accessToken;
        }

        // 构建请求URL
        $requestUrl = $this->buildRequestUrl($config, $urlPath, $method, $appKey, $sign, $timestamp, $accessTokenStr);

        // 记录请求详情（调试模式）
        if ($config->debug) {
            $config->logger->debug('Request details', [
                'url'       => $requestUrl,
                'method'    => $method,
                'params'    => $paramJson,
                'timestamp' => $timestamp,
            ]);
        }

        // 发送HTTP请求
        $httpRequest                 = new HttpRequest();
        $httpRequest->url            = $requestUrl;
        $httpRequest->body           = $paramJson;
        $httpRequest->connectTimeout = $config->httpConnectTimeout;
        $httpRequest->readTimeout    = $config->httpReadTimeout;

        $httpResponse = $this->httpClient->post($httpRequest);

        return json_decode($httpResponse->body, false, 512, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 构建请求URL.
     *
     * @param DouDianOpConfig $config 配置对象
     * @param string $urlPath URL路径
     * @param string $method 方法名
     * @param string $appKey 应用Key
     * @param string $sign 签名
     * @param int $timestamp 时间戳
     * @param string $accessTokenStr 访问令牌字符串
     *
     * @return string 完整的请求URL
     */
    private function buildRequestUrl(DouDianOpConfig $config, $urlPath, $method, $appKey, $sign, $timestamp, $accessTokenStr)
    {
        $params = [
            'app_key'      => $appKey,
            'method'       => $method,
            'v'            => $config->apiVersion,
            'sign'         => $sign,
            'timestamp'    => $timestamp,
            'access_token' => $accessTokenStr,
            'sign_method'  => $config->signMethod,
        ];

        return $config->openRequestUrl . $urlPath . '?' . http_build_query($params);
    }

    /**
     * 检查API响应.
     *
     * @param mixed $response API响应
     * @param DouDianOpConfig $config 配置对象
     *
     * @throws ApiException
     */
    private function checkApiResponse($response, DouDianOpConfig $config)
    {
        if (!is_object($response)) {
            throw new ApiException('Invalid response format', '', '', 1002);
        }

        // 检查是否有错误信息
        if (isset($response->err_no) && 0 != $response->err_no) {
            $message = $response->message ?? 'Unknown API error';
            $logId   = $response->log_id ?? '';

            throw new ApiException($message, (string) $response->err_no, $logId, 1003);
        }
    }

    /**
     * 遮蔽访问令牌（用于日志记录）.
     *
     * @param mixed $accessToken 访问令牌
     *
     * @return string 遮蔽后的令牌
     */
    private function maskToken($accessToken)
    {
        if (empty($accessToken)) {
            return 'none';
        }

        $token = is_object($accessToken) ? $accessToken->getAccessToken() : $accessToken;

        if (strlen($token) <= 8) {
            return str_repeat('*', strlen($token));
        }

        return substr($token, 0, 4) . str_repeat('*', strlen($token) - 8) . substr($token, -4);
    }

    private function getMethod($urlPath)
    {
        if (0 == strlen($urlPath)) {
            return $urlPath;
        }
        $methodPath = '';

        if ('/' == substr($urlPath, 0, 1)) {
            $methodPath = substr($urlPath, 1, strlen($urlPath));
        } else {
            $methodPath = $urlPath;
        }

        return str_replace('/', '.', $methodPath);
    }
}
