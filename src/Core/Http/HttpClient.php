<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Core\Http;

use DouDianSdk\Core\Exception\HttpException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class HttpClient implements HttpClientInterface
{
    /**
     * @var static|null 单例实例（FPM 环境兼容）
     */
    private static $instance;

    /**
     * @var Client Guzzle HTTP 客户端
     */
    private $client;

    /**
     * @var array 默认HTTP头
     */
    private $defaultHeaders = [
        'Content-Type' => 'application/json; charset=utf-8',
        'Accept'       => 'application/json',
        'User-Agent'   => 'DouDianSDK-PHP/2.1.0',
        'from'         => 'sdk',
        'sdk-type'     => 'php',
    ];

    /**
     * 构造函数.
     *
     * @param array $config Guzzle 客户端配置
     */
    public function __construct(array $config = [])
    {
        $defaultConfig = [
            'timeout'         => 30,
            'connect_timeout' => 10,
            'verify'          => false,
            'http_errors'     => false,
            'headers'         => $this->defaultHeaders,
            'allow_redirects' => [
                'max'       => 3,
                'strict'    => true,
                'referer'   => true,
                'protocols' => ['http', 'https'],
            ],
        ];

        $this->client = new Client(array_merge($defaultConfig, $config));
    }

    /**
     * 发送POST请求
     *
     * @param HttpRequest $httpRequest HTTP请求对象
     *
     * @return HttpResponse HTTP响应对象
     *
     * @throws HttpException 当HTTP请求失败时抛出异常
     */
    public function post(HttpRequest $httpRequest): HttpResponse
    {
        try {
            $options         = $this->buildRequestOptions($httpRequest);
            $options['body'] = $httpRequest->body;

            $response = $this->client->post($httpRequest->url, $options);

            return $this->buildHttpResponse($response, $httpRequest);
        } catch (RequestException $e) {
            throw new HttpException('HTTP Request failed: ' . $e->getMessage(), $e->getResponse() ? $e->getResponse()->getStatusCode() : 0, $e->getResponse() ? $e->getResponse()->getBody()->getContents() : '', 0, $e);
        } catch (GuzzleException $e) {
            throw new HttpException('HTTP Client Error: ' . $e->getMessage(), 0, '', 0, $e);
        }
    }

    /**
     * 发送GET请求
     *
     * @param HttpRequest $httpRequest HTTP请求对象
     *
     * @return HttpResponse HTTP响应对象
     *
     * @throws HttpException 当HTTP请求失败时抛出异常
     */
    public function get(HttpRequest $httpRequest): HttpResponse
    {
        try {
            $options = $this->buildRequestOptions($httpRequest);

            $response = $this->client->get($httpRequest->url, $options);

            return $this->buildHttpResponse($response, $httpRequest);
        } catch (RequestException $e) {
            throw new HttpException('HTTP Request failed: ' . $e->getMessage(), $e->getResponse() ? $e->getResponse()->getStatusCode() : 0, $e->getResponse() ? $e->getResponse()->getBody()->getContents() : '', 0, $e);
        } catch (GuzzleException $e) {
            throw new HttpException('HTTP Client Error: ' . $e->getMessage(), 0, '', 0, $e);
        }
    }

    /**
     * 发送PUT请求
     *
     * @param HttpRequest $httpRequest HTTP请求对象
     *
     * @return HttpResponse HTTP响应对象
     *
     * @throws HttpException 当HTTP请求失败时抛出异常
     */
    public function put(HttpRequest $httpRequest): HttpResponse
    {
        try {
            $options         = $this->buildRequestOptions($httpRequest);
            $options['body'] = $httpRequest->body;

            $response = $this->client->put($httpRequest->url, $options);

            return $this->buildHttpResponse($response, $httpRequest);
        } catch (RequestException $e) {
            throw new HttpException('HTTP Request failed: ' . $e->getMessage(), $e->getResponse() ? $e->getResponse()->getStatusCode() : 0, $e->getResponse() ? $e->getResponse()->getBody()->getContents() : '', 0, $e);
        } catch (GuzzleException $e) {
            throw new HttpException('HTTP Client Error: ' . $e->getMessage(), 0, '', 0, $e);
        }
    }

    /**
     * 发送DELETE请求
     *
     * @param HttpRequest $httpRequest HTTP请求对象
     *
     * @return HttpResponse HTTP响应对象
     *
     * @throws HttpException 当HTTP请求失败时抛出异常
     */
    public function delete(HttpRequest $httpRequest): HttpResponse
    {
        try {
            $options = $this->buildRequestOptions($httpRequest);

            $response = $this->client->delete($httpRequest->url, $options);

            return $this->buildHttpResponse($response, $httpRequest);
        } catch (RequestException $e) {
            throw new HttpException('HTTP Request failed: ' . $e->getMessage(), $e->getResponse() ? $e->getResponse()->getStatusCode() : 0, $e->getResponse() ? $e->getResponse()->getBody()->getContents() : '', 0, $e);
        } catch (GuzzleException $e) {
            throw new HttpException('HTTP Client Error: ' . $e->getMessage(), 0, '', 0, $e);
        }
    }

    /**
     * 构建请求选项.
     *
     * @param HttpRequest $httpRequest HTTP请求对象
     *
     * @return array 请求选项
     */
    private function buildRequestOptions(HttpRequest $httpRequest): array
    {
        $options = [
            'timeout'         => $httpRequest->readTimeout / 1000, // 转换为秒
            'connect_timeout' => $httpRequest->connectTimeout / 1000, // 转换为秒
            'headers'         => array_merge($this->defaultHeaders, $httpRequest->headers),
        ];

        // 添加查询参数
        if (!empty($httpRequest->queryParams)) {
            $options['query'] = $httpRequest->queryParams;
        }

        return $options;
    }

    /**
     * 构建HTTP响应对象
     *
     * @param ResponseInterface $response Guzzle响应对象
     * @param HttpRequest $httpRequest HTTP请求对象
     *
     * @return HttpResponse HTTP响应对象
     */
    private function buildHttpResponse(ResponseInterface $response, HttpRequest $httpRequest): HttpResponse
    {
        $httpResponse             = new HttpResponse();
        $httpResponse->statusCode = $response->getStatusCode();
        $httpResponse->body       = $response->getBody()->getContents();

        // 设置响应头
        $headers = [];

        foreach ($response->getHeaders() as $name => $values) {
            $headers[$name] = implode(', ', $values);
        }
        $httpResponse->setHeaders($headers);

        // 记录请求信息
        $httpResponse->requestInfo = [
            'protocol'      => $response->getProtocolVersion(),
            'reason_phrase' => $response->getReasonPhrase(),
            'effective_url' => $httpRequest->url,
        ];

        // 检查HTTP状态码
        if ($httpResponse->statusCode >= 400) {
            throw new HttpException("HTTP Request failed with status {$httpResponse->statusCode}", $httpResponse->statusCode, $httpResponse->body);
        }

        return $httpResponse;
    }

    /**
     * 获取默认实例（单例模式，保持向后兼容）.
     *
     * @param array $config 客户端配置
     * @return HttpClientInterface
     * @deprecated 使用 HttpClientFactory::getInstance() 代替
     */
    public static function getInstance(array $config = []): HttpClientInterface
    {
        // 优先使用工厂方法
        return HttpClientFactory::getInstance($config);
    }

    /**
     * 创建新实例（内部使用）
     *
     * @param array $config 客户端配置
     * @return HttpClient
     */
    public static function createInstance(array $config = []): HttpClient
    {
        return new self($config);
    }

    /**
     * 重置单例实例（主要用于测试）
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
        HttpClientFactory::reset();
    }

    /**
     * 获取Guzzle客户端.
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * 设置默认请求头.
     *
     * @param array $headers 请求头
     */
    public function setDefaultHeaders(array $headers): HttpClientInterface
    {
        $this->defaultHeaders = array_merge($this->defaultHeaders, $headers);

        return $this;
    }

    /**
     * 获取默认请求头.
     */
    public function getDefaultHeaders(): array
    {
        return $this->defaultHeaders;
    }
}
