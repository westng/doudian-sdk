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
use DouDianSdk\Core\Swoole\ConnectionPool;
use DouDianSdk\Core\Swoole\PoolConfig;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;

/**
 * Swoole 协程安全的 HTTP 客户端
 * 
 * 策略：
 * 1. 优先使用 Hyperf PoolHandler（内置连接池，最优方案）
 * 2. 其次使用 SDK ConnectionPool + Hyperf CoroutineHandler
 * 3. 最后使用 SDK ConnectionPool + 原生 Swoole
 */
class SwooleHttpClient implements HttpClientInterface
{
    /**
     * @var Client|null Guzzle 客户端（Hyperf PoolHandler 模式）
     */
    private $client;

    /**
     * @var ConnectionPool|null 连接池（非 Hyperf 模式）
     */
    private $pool;

    /**
     * @var PoolConfig 连接池配置
     */
    private $poolConfig;

    /**
     * @var bool 是否使用 Hyperf PoolHandler
     */
    private $useHyperfPool = false;

    /**
     * @var array 默认 HTTP 头
     */
    private $defaultHeaders = [
        'Content-Type' => 'application/json; charset=utf-8',
        'Accept'       => 'application/json',
        'User-Agent'   => 'DouDianSDK-PHP/2.1.1-Swoole',
        'from'         => 'sdk',
        'sdk-type'     => 'php',
    ];

    /**
     * @var array 统计信息
     */
    private $stats = [
        'total_requests' => 0,
        'mode' => 'unknown',
    ];

    /**
     * 构造函数
     *
     * @param array $config Guzzle 配置
     * @param PoolConfig|null $poolConfig 连接池配置
     */
    public function __construct(array $config = [], ?PoolConfig $poolConfig = null)
    {
        $this->poolConfig = $poolConfig ?? new PoolConfig();
        $this->poolConfig->validate();

        $this->initializeClient($config);
    }

    /**
     * 初始化客户端
     * 
     * 优先级：Hyperf PoolHandler > SDK Pool + CoroutineHandler > SDK Pool
     */
    private function initializeClient(array $config): void
    {
        // 方案1：Hyperf PoolHandler（最优，内置连接池）
        if (class_exists('\Hyperf\Guzzle\PoolHandler')) {
            $this->useHyperfPool = true;
            $this->stats['mode'] = 'hyperf-pool-handler';
            $this->client = $this->createHyperfPoolClient($config);
            return;
        }

        // 方案2/3：使用 SDK ConnectionPool
        $this->useHyperfPool = false;
        $this->pool = ConnectionPool::getInstance($this->poolConfig, $config);
        
        if (class_exists('\Hyperf\Guzzle\CoroutineHandler')) {
            $this->stats['mode'] = 'sdk-pool-with-hyperf-handler';
        } else {
            $this->stats['mode'] = 'sdk-pool-native';
        }
    }

    /**
     * 创建使用 Hyperf PoolHandler 的客户端
     * 
     * 兼容 Hyperf 2.x 和 3.x：
     * - Hyperf 2.x: PoolHandler 构造函数接受配置数组
     * - Hyperf 3.x: PoolHandler 构造函数需要 PoolFactory 实例
     */
    private function createHyperfPoolClient(array $config): Client
    {
        $handler = $this->createPoolHandler();

        $stack = HandlerStack::create($handler);

        return new Client(array_merge([
            'handler'         => $stack,
            'timeout'         => 30,
            'connect_timeout' => 10,
            'verify'          => false,
            'http_errors'     => false,
            'headers'         => $this->defaultHeaders,
        ], $config));
    }

    /**
     * 创建 PoolHandler 实例（兼容 Hyperf 2.x 和 3.x）
     * 
     * @return \Hyperf\Guzzle\PoolHandler
     */
    private function createPoolHandler()
    {
        $poolOptions = [
            'min_connections' => 1,
            'max_connections' => $this->poolConfig->maxConnections,
            'wait_timeout'    => $this->poolConfig->waitTimeout,
            'max_idle_time'   => $this->poolConfig->maxIdleTime,
        ];

        // Hyperf 3.x: 需要 PoolFactory 实例
        if (class_exists('\Hyperf\Guzzle\ClientFactory') && class_exists('\Hyperf\Pool\SimplePool\PoolFactory')) {
            try {
                $container = \Hyperf\Context\ApplicationContext::getContainer();
                $poolFactory = $container->get(\Hyperf\Pool\SimplePool\PoolFactory::class);
                return new \Hyperf\Guzzle\PoolHandler($poolFactory, $poolOptions);
            } catch (\Throwable $e) {
                // 容器未初始化或获取失败，回退到 2.x 方式
            }
        }

        // Hyperf 2.x: 直接传配置数组
        return new \Hyperf\Guzzle\PoolHandler($poolOptions);
    }

    /**
     * 发送 POST 请求
     */
    public function post(HttpRequest $httpRequest): HttpResponse
    {
        return $this->request('POST', $httpRequest);
    }

    /**
     * 发送 GET 请求
     */
    public function get(HttpRequest $httpRequest): HttpResponse
    {
        return $this->request('GET', $httpRequest);
    }

    /**
     * 发送 PUT 请求
     */
    public function put(HttpRequest $httpRequest): HttpResponse
    {
        return $this->request('PUT', $httpRequest);
    }

    /**
     * 发送 DELETE 请求
     */
    public function delete(HttpRequest $httpRequest): HttpResponse
    {
        return $this->request('DELETE', $httpRequest);
    }

    /**
     * 发送 HTTP 请求
     */
    private function request(string $method, HttpRequest $httpRequest): HttpResponse
    {
        $this->stats['total_requests']++;

        // Hyperf PoolHandler 模式：直接使用客户端
        if ($this->useHyperfPool) {
            return $this->requestWithHyperfPool($method, $httpRequest);
        }

        // SDK ConnectionPool 模式：借还连接
        return $this->requestWithSdkPool($method, $httpRequest);
    }

    /**
     * 使用 Hyperf PoolHandler 发送请求
     */
    private function requestWithHyperfPool(string $method, HttpRequest $httpRequest): HttpResponse
    {
        try {
            $options = $this->buildRequestOptions($httpRequest);

            if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
                $options['body'] = $httpRequest->body;
            }

            $response = $this->client->request($method, $httpRequest->url, $options);

            return $this->buildHttpResponse($response, $httpRequest);
        } catch (RequestException $e) {
            throw new HttpException(
                'HTTP Request failed: ' . $e->getMessage(),
                $e->getResponse() ? $e->getResponse()->getStatusCode() : 0,
                $e->getResponse() ? $e->getResponse()->getBody()->getContents() : '',
                0,
                $e
            );
        } catch (GuzzleException $e) {
            throw new HttpException('HTTP Client Error: ' . $e->getMessage(), 0, '', 0, $e);
        }
    }

    /**
     * 使用 SDK ConnectionPool 发送请求
     */
    private function requestWithSdkPool(string $method, HttpRequest $httpRequest): HttpResponse
    {
        $client = null;
        
        try {
            $client = $this->pool->get();

            $options = $this->buildRequestOptions($httpRequest);

            if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
                $options['body'] = $httpRequest->body;
            }

            $response = $client->request($method, $httpRequest->url, $options);

            return $this->buildHttpResponse($response, $httpRequest);
        } catch (RequestException $e) {
            throw new HttpException(
                'HTTP Request failed: ' . $e->getMessage(),
                $e->getResponse() ? $e->getResponse()->getStatusCode() : 0,
                $e->getResponse() ? $e->getResponse()->getBody()->getContents() : '',
                0,
                $e
            );
        } catch (GuzzleException $e) {
            throw new HttpException('HTTP Client Error: ' . $e->getMessage(), 0, '', 0, $e);
        } catch (\RuntimeException $e) {
            throw new HttpException('Connection pool error: ' . $e->getMessage(), 0, '', 0, $e);
        } finally {
            if ($client !== null && $this->pool !== null) {
                $this->pool->put($client);
            }
        }
    }

    /**
     * 构建请求选项
     */
    private function buildRequestOptions(HttpRequest $httpRequest): array
    {
        $options = [
            'timeout'         => $httpRequest->readTimeout / 1000,
            'connect_timeout' => $httpRequest->connectTimeout / 1000,
            'headers'         => array_merge($this->defaultHeaders, $httpRequest->headers),
        ];

        if (!empty($httpRequest->queryParams)) {
            $options['query'] = $httpRequest->queryParams;
        }

        return $options;
    }

    /**
     * 构建 HTTP 响应对象
     */
    private function buildHttpResponse(ResponseInterface $response, HttpRequest $httpRequest): HttpResponse
    {
        $httpResponse = new HttpResponse();
        $httpResponse->statusCode = $response->getStatusCode();
        $httpResponse->body = $response->getBody()->getContents();

        $headers = [];
        foreach ($response->getHeaders() as $name => $values) {
            $headers[$name] = implode(', ', $values);
        }
        $httpResponse->setHeaders($headers);

        $httpResponse->requestInfo = [
            'protocol'      => $response->getProtocolVersion(),
            'reason_phrase' => $response->getReasonPhrase(),
            'effective_url' => $httpRequest->url,
        ];

        if ($httpResponse->statusCode >= 400) {
            throw new HttpException(
                "HTTP Request failed with status {$httpResponse->statusCode}",
                $httpResponse->statusCode,
                $httpResponse->body
            );
        }

        return $httpResponse;
    }

    /**
     * 获取默认请求头
     */
    public function getDefaultHeaders(): array
    {
        return $this->defaultHeaders;
    }

    /**
     * 设置默认请求头
     */
    public function setDefaultHeaders(array $headers): HttpClientInterface
    {
        $this->defaultHeaders = array_merge($this->defaultHeaders, $headers);
        return $this;
    }

    /**
     * 获取统计信息
     */
    public function getStats(): array
    {
        $stats = $this->stats;
        $stats['use_hyperf_pool'] = $this->useHyperfPool;
        $stats['pool_config'] = [
            'max_connections' => $this->poolConfig->maxConnections,
            'max_idle_time'   => $this->poolConfig->maxIdleTime,
            'wait_timeout'    => $this->poolConfig->waitTimeout,
        ];

        // 如果使用 SDK Pool，添加连接池统计
        if (!$this->useHyperfPool && $this->pool !== null) {
            $poolStats = $this->pool->getStats();
            $stats['pool_size'] = $poolStats['pool_size'];
            $stats['active_connections'] = $poolStats['active_connections'];
            $stats['idle_connections'] = $poolStats['idle_connections'];
            $stats['wait_queue_size'] = $poolStats['wait_queue_size'];
            $stats['total_created'] = $poolStats['total_created'];
        }

        return $stats;
    }

    /**
     * 关闭连接
     */
    public function close(): void
    {
        if ($this->pool !== null) {
            $this->pool->close();
        }
        $this->client = null;
    }

    /**
     * 获取连接池配置
     */
    public function getPoolConfig(): PoolConfig
    {
        return $this->poolConfig;
    }

    /**
     * 是否使用 Hyperf PoolHandler
     */
    public function isUsingHyperfPool(): bool
    {
        return $this->useHyperfPool;
    }
}
