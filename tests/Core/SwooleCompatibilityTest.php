<?php

/**
 * Swoole 兼容性测试
 */

namespace DouDianSdk\Tests\Core;

use DouDianSdk\Core\Client\DouDianOpClient;
use DouDianSdk\Core\Client\DouDianSdk;
use DouDianSdk\Core\Config\GlobalConfig;
use DouDianSdk\Core\Http\HttpClient;
use DouDianSdk\Core\Http\HttpClientFactory;
use DouDianSdk\Core\Http\HttpClientInterface;
use DouDianSdk\Core\Swoole\PoolConfig;
use DouDianSdk\Core\Swoole\RuntimeDetector;
use DouDianSdk\Tests\TestCase;

class SwooleCompatibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // 重置所有单例
        HttpClientFactory::reset();
        DouDianOpClient::resetInstance();
        GlobalConfig::resetInstance();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        HttpClientFactory::reset();
        DouDianOpClient::resetInstance();
        GlobalConfig::resetInstance();
    }

    /**
     * 测试环境检测
     */
    public function testRuntimeDetector(): void
    {
        // 在非 Swoole 环境下应该返回 false
        $this->assertFalse(RuntimeDetector::inCoroutine());
        $this->assertEquals(-1, RuntimeDetector::getCoroutineId());
        
        // swooleLoaded 取决于是否安装了 Swoole 扩展
        $this->assertIsBool(RuntimeDetector::swooleLoaded());
    }

    /**
     * 测试连接池配置
     */
    public function testPoolConfig(): void
    {
        $config = new PoolConfig();
        
        // 测试默认值
        $this->assertEquals(50, $config->maxConnections);
        $this->assertEquals(60, $config->maxIdleTime);
        $this->assertEquals(3.0, $config->waitTimeout);
        
        // 测试从数组创建
        $config = PoolConfig::fromArray([
            'max_connections' => 100,
            'max_idle_time' => 120,
            'wait_timeout' => 5.0,
        ]);
        
        $this->assertEquals(100, $config->maxConnections);
        $this->assertEquals(120, $config->maxIdleTime);
        $this->assertEquals(5.0, $config->waitTimeout);
        
        // 测试验证通过
        $config->validate();
        $this->assertTrue(true);
    }

    /**
     * 测试无效配置抛出异常
     */
    public function testPoolConfigValidationFails(): void
    {
        $config = new PoolConfig();
        $config->maxConnections = 0;
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('max_connections must be greater than 0');
        $config->validate();
    }

    /**
     * 测试连接池配置超出范围
     */
    public function testPoolConfigMaxConnectionsExceeded(): void
    {
        $config = new PoolConfig();
        $config->maxConnections = 1001;
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('max_connections must not exceed 1000');
        $config->validate();
    }

    /**
     * 测试 HttpClientFactory 在 FPM 环境下返回正确类型
     */
    public function testHttpClientFactoryInFpmEnvironment(): void
    {
        $client = HttpClientFactory::getInstance();
        
        $this->assertInstanceOf(HttpClientInterface::class, $client);
        
        // 在非 Swoole 协程环境下，根据是否加载 Swoole 扩展返回不同类型
        // 如果 Swoole 扩展已加载，会返回 SwooleHttpClient（使用共享连接池）
        // 如果 Swoole 扩展未加载，会返回 HttpClient
        if (!RuntimeDetector::swooleLoaded()) {
            $this->assertInstanceOf(HttpClient::class, $client);
        }
    }

    /**
     * 测试 HttpClientFactory 单例行为
     */
    public function testHttpClientFactorySingleton(): void
    {
        $client1 = HttpClientFactory::getInstance();
        $client2 = HttpClientFactory::getInstance();
        
        // 在 FPM 环境下应该返回同一个实例
        if (!RuntimeDetector::inCoroutine()) {
            $this->assertSame($client1, $client2);
        }
    }

    /**
     * 测试 SDK 向后兼容性
     */
    public function testSdkBackwardCompatibility(): void
    {
        // 测试原有的构造方式仍然有效
        $sdk = new DouDianSdk('test_app_key', 'test_app_secret');
        
        $this->assertInstanceOf(DouDianSdk::class, $sdk);
        $this->assertInstanceOf(GlobalConfig::class, $sdk->getConfig());
        
        // 测试配置是否正确设置
        $config = $sdk->getConfig();
        $this->assertEquals('test_app_key', $config->appKey);
        $this->assertEquals('test_app_secret', $config->appSecret);
    }

    /**
     * 测试 SDK 带连接池配置
     */
    public function testSdkWithPoolConfig(): void
    {
        $sdk = new DouDianSdk('test_app_key', 'test_app_secret', [
            'pool' => [
                'max_connections' => 100,
                'max_idle_time' => 120,
                'wait_timeout' => 5.0,
            ],
        ]);
        
        $config = $sdk->getConfig();
        $this->assertEquals(100, $config->poolMaxConnections);
        $this->assertEquals(120, $config->poolMaxIdleTime);
        $this->assertEquals(5.0, $config->poolWaitTimeout);
    }

    /**
     * 测试 SDK 新增方法
     */
    public function testSdkNewMethods(): void
    {
        $sdk = new DouDianSdk('test_app_key', 'test_app_secret');
        
        // 测试 getEnvironment
        $env = $sdk->getEnvironment();
        $this->assertIsString($env);
        $this->assertContains($env, ['swoole-coroutine', 'swoole-sync', 'fpm']);
        
        // 测试 isSwooleCoroutine
        $this->assertIsBool($sdk->isSwooleCoroutine());
        
        // 测试 getPoolStats
        $stats = $sdk->getPoolStats();
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('pool_size', $stats);
        $this->assertArrayHasKey('environment', $stats);
        
        // 测试 setPoolConfig
        $sdk->setPoolConfig(200, 180, 10.0);
        $config = $sdk->getConfig();
        $this->assertEquals(200, $config->poolMaxConnections);
    }

    /**
     * 测试 DouDianOpClient 单例
     */
    public function testDouDianOpClientSingleton(): void
    {
        $client1 = DouDianOpClient::getInstance();
        $client2 = DouDianOpClient::getInstance();
        
        // 在 FPM 环境下应该返回同一个实例
        if (!RuntimeDetector::inCoroutine()) {
            $this->assertSame($client1, $client2);
        }
    }

    /**
     * 测试 GlobalConfig 单例
     */
    public function testGlobalConfigSingleton(): void
    {
        $config1 = GlobalConfig::getGlobalConfig();
        $config2 = GlobalConfig::getInstance();
        
        // 在 FPM 环境下应该返回同一个实例
        if (!RuntimeDetector::inCoroutine()) {
            $this->assertSame($config1, $config2);
        }
    }

    /**
     * 测试 shutdown 方法
     */
    public function testShutdown(): void
    {
        $sdk = new DouDianSdk('test_app_key', 'test_app_secret');
        
        // 调用 shutdown 不应该抛出异常
        $sdk->shutdown();
        $this->assertTrue(true);
    }

    /**
     * 测试版本号更新
     */
    public function testVersionUpdated(): void
    {
        $sdk = new DouDianSdk();
        $this->assertEquals('2.1.0', $sdk->getVersion());
    }
}
