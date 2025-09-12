<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Tests\Http;

use DouDianSdk\Core\Exception\HttpException;
use DouDianSdk\Core\Http\HttpClient;
use DouDianSdk\Core\Http\HttpRequest;
use DouDianSdk\Core\Http\HttpResponse;
use DouDianSdk\Tests\TestCase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\Psr7\Response;

class HttpClientTest extends TestCase
{
    /**
     * @var HttpClient HTTP客户端
     */
    private $httpClient;

    /**
     * @var MockHandler Mock处理器
     */
    private $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();

        // 重置单例实例
        $reflection       = new \ReflectionClass(HttpClient::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);

        // 创建Mock处理器
        $this->mockHandler = new MockHandler();
        $handlerStack      = HandlerStack::create($this->mockHandler);

        // 创建HTTP客户端
        $this->httpClient = HttpClient::getInstance([
            'handler' => $handlerStack,
        ]);
    }

    public function testPostSuccess(): void
    {
        // 设置Mock响应
        $this->mockHandler->append(new Response(200, [], '{"success": true}'));

        // 创建请求
        $request                 = new HttpRequest();
        $request->url            = 'https://api.example.com/test';
        $request->body           = '{"test": "data"}';
        $request->connectTimeout = 1000;
        $request->readTimeout    = 5000;

        // 发送请求
        $response = $this->httpClient->post($request);

        // 验证响应
        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals('{"success": true}', $response->body);
        $this->assertTrue($response->isSuccess());
    }

    public function testGetSuccess(): void
    {
        // 设置Mock响应
        $this->mockHandler->append(new Response(200, [], '{"data": "test"}'));

        // 创建请求
        $request                 = new HttpRequest();
        $request->url            = 'https://api.example.com/test';
        $request->connectTimeout = 1000;
        $request->readTimeout    = 5000;

        // 发送请求
        $response = $this->httpClient->get($request);

        // 验证响应
        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals('{"data": "test"}', $response->body);
    }

    public function testPutSuccess(): void
    {
        // 设置Mock响应
        $this->mockHandler->append(new Response(200, [], '{"updated": true}'));

        // 创建请求
        $request                 = new HttpRequest();
        $request->url            = 'https://api.example.com/test';
        $request->body           = '{"update": "data"}';
        $request->connectTimeout = 1000;
        $request->readTimeout    = 5000;

        // 发送请求
        $response = $this->httpClient->put($request);

        // 验证响应
        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals('{"updated": true}', $response->body);
    }

    public function testDeleteSuccess(): void
    {
        // 设置Mock响应
        $this->mockHandler->append(new Response(204, [], ''));

        // 创建请求
        $request                 = new HttpRequest();
        $request->url            = 'https://api.example.com/test';
        $request->connectTimeout = 1000;
        $request->readTimeout    = 5000;

        // 发送请求
        $response = $this->httpClient->delete($request);

        // 验证响应
        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertEquals(204, $response->statusCode);
        $this->assertEquals('', $response->body);
    }

    public function testHttpError(): void
    {
        // 设置Mock响应
        $this->mockHandler->append(new Response(404, [], '{"error": "Not Found"}'));

        // 创建请求
        $request                 = new HttpRequest();
        $request->url            = 'https://api.example.com/notfound';
        $request->connectTimeout = 1000;
        $request->readTimeout    = 5000;

        // 验证异常
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('HTTP Request failed with status 404');

        $this->httpClient->get($request);
    }

    public function testRequestException(): void
    {
        // 设置Mock异常
        $this->mockHandler->append(
            new RequestException(
                'Connection failed',
                new PsrRequest('GET', 'https://api.example.com/test')
            )
        );

        // 创建请求
        $request                 = new HttpRequest();
        $request->url            = 'https://api.example.com/test';
        $request->connectTimeout = 1000;
        $request->readTimeout    = 5000;

        // 验证异常
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('HTTP Request failed: Connection failed');

        $this->httpClient->get($request);
    }

    public function testCustomHeaders(): void
    {
        // 设置Mock响应
        $this->mockHandler->append(new Response(200, [], '{"success": true}'));

        // 创建请求
        $request                 = new HttpRequest();
        $request->url            = 'https://api.example.com/test';
        $request->headers        = ['X-Custom-Header' => 'test-value'];
        $request->connectTimeout = 1000;
        $request->readTimeout    = 5000;

        // 发送请求
        $response = $this->httpClient->post($request);

        // 验证响应
        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertEquals(200, $response->statusCode);
    }

    public function testQueryParams(): void
    {
        // 设置Mock响应
        $this->mockHandler->append(new Response(200, [], '{"success": true}'));

        // 创建请求
        $request                 = new HttpRequest();
        $request->url            = 'https://api.example.com/test';
        $request->queryParams    = ['param1' => 'value1', 'param2' => 'value2'];
        $request->connectTimeout = 1000;
        $request->readTimeout    = 5000;

        // 发送请求
        $response = $this->httpClient->get($request);

        // 验证响应
        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertEquals(200, $response->statusCode);
    }

    public function testResponseHeaders(): void
    {
        // 设置Mock响应
        $this->mockHandler->append(new Response(200, [
            'Content-Type'      => 'application/json',
            'X-Custom-Response' => 'test-value',
        ], '{"success": true}'));

        // 创建请求
        $request                 = new HttpRequest();
        $request->url            = 'https://api.example.com/test';
        $request->connectTimeout = 1000;
        $request->readTimeout    = 5000;

        // 发送请求
        $response = $this->httpClient->get($request);

        // 验证响应头
        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals('test-value', $response->getHeader('X-Custom-Response'));
    }

    public function testJsonResponse(): void
    {
        // 设置Mock响应
        $this->mockHandler->append(new Response(200, [], '{"success": true, "data": {"id": 123}}'));

        // 创建请求
        $request                 = new HttpRequest();
        $request->url            = 'https://api.example.com/test';
        $request->connectTimeout = 1000;
        $request->readTimeout    = 5000;

        // 发送请求
        $response = $this->httpClient->get($request);

        // 验证JSON解析
        $jsonData = $response->getJson();
        $this->assertIsArray($jsonData);
        $this->assertTrue($jsonData['success']);
        $this->assertEquals(123, $jsonData['data']['id']);
    }

    public function testSingletonInstance(): void
    {
        // 获取实例
        $instance1 = HttpClient::getInstance();
        $instance2 = HttpClient::getInstance();

        // 验证单例模式
        $this->assertSame($instance1, $instance2);
    }

    public function testDefaultHeaders(): void
    {
        // 获取客户端
        $client = HttpClient::getInstance();

        // 设置默认头
        $client->setDefaultHeaders(['X-Test' => 'value']);

        // 验证默认头
        $headers = $client->getDefaultHeaders();
        $this->assertArrayHasKey('X-Test', $headers);
        $this->assertEquals('value', $headers['X-Test']);
    }
}
