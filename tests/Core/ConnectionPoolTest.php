<?php

/**
 * 连接池测试
 */

namespace DouDianSdk\Tests\Core;

use DouDianSdk\Core\Swoole\ConnectionPool;
use DouDianSdk\Core\Swoole\PoolConfig;
use DouDianSdk\Tests\TestCase;
use GuzzleHttp\Client;

class ConnectionPoolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ConnectionPool::reset();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        ConnectionPool::reset();
    }

    /**
     * 测试连接池单例
     */
    public function testConnectionPoolSingleton(): void
    {
        $pool1 = ConnectionPool::getInstance();
        $pool2 = ConnectionPool::getInstance();

        $this->assertSame($pool1, $pool2);
    }

    /**
     * 测试获取和归还客户端
     */
    public function testGetAndPutClient(): void
    {
        $config = new PoolConfig();
        $config->maxConnections = 5;

        $pool = ConnectionPool::getInstance($config);

        // 获取客户端
        $client = $pool->get();
        $this->assertInstanceOf(Client::class, $client);

        // 检查统计
        $stats = $pool->getStats();
        $this->assertEquals(1, $stats['active_connections']);
        $this->assertEquals(1, $stats['total_created']);

        // 归还客户端
        $pool->put($client);

        $stats = $pool->getStats();
        $this->assertEquals(0, $stats['active_connections']);
        $this->assertEquals(1, $stats['idle_connections']);
    }

    /**
     * 测试连接复用
     */
    public function testConnectionReuse(): void
    {
        $config = new PoolConfig();
        $config->maxConnections = 2;

        $pool = ConnectionPool::getInstance($config);

        // 获取第一个客户端
        $client1 = $pool->get();
        $pool->put($client1);

        // 再次获取，应该复用
        $client2 = $pool->get();
        $this->assertSame($client1, $client2);

        $stats = $pool->getStats();
        $this->assertEquals(1, $stats['total_created']); // 只创建了一个
        $this->assertEquals(2, $stats['total_requests']); // 请求了两次

        $pool->put($client2);
    }

    /**
     * 测试连接池统计
     */
    public function testPoolStats(): void
    {
        $config = new PoolConfig();
        $config->maxConnections = 10;

        $pool = ConnectionPool::getInstance($config);

        $stats = $pool->getStats();

        $this->assertArrayHasKey('pool_size', $stats);
        $this->assertArrayHasKey('total_created', $stats);
        $this->assertArrayHasKey('active_connections', $stats);
        $this->assertArrayHasKey('idle_connections', $stats);
        $this->assertArrayHasKey('wait_queue_size', $stats);
        $this->assertArrayHasKey('total_requests', $stats);
        $this->assertArrayHasKey('closed', $stats);

        $this->assertEquals(10, $stats['pool_size']);
        $this->assertFalse($stats['closed']);
    }

    /**
     * 测试关闭连接池
     */
    public function testClosePool(): void
    {
        $pool = ConnectionPool::getInstance();

        // 获取一些客户端
        $client1 = $pool->get();
        $client2 = $pool->get();
        $pool->put($client1);
        $pool->put($client2);

        // 关闭连接池
        $pool->close();

        $stats = $pool->getStats();
        $this->assertTrue($stats['closed']);
        $this->assertEquals(0, $stats['idle_connections']);
    }

    /**
     * 测试关闭后获取客户端抛出异常
     */
    public function testGetAfterClose(): void
    {
        $pool = ConnectionPool::getInstance();
        $pool->close();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Connection pool is closed');

        $pool->get();
    }

    /**
     * 测试多个客户端并发获取
     */
    public function testMultipleClients(): void
    {
        $config = new PoolConfig();
        $config->maxConnections = 5;

        $pool = ConnectionPool::getInstance($config);

        $clients = [];
        for ($i = 0; $i < 5; $i++) {
            $clients[] = $pool->get();
        }

        $stats = $pool->getStats();
        $this->assertEquals(5, $stats['active_connections']);
        $this->assertEquals(5, $stats['total_created']);
        $this->assertEquals(0, $stats['idle_connections']);

        // 归还所有客户端
        foreach ($clients as $client) {
            $pool->put($client);
        }

        $stats = $pool->getStats();
        $this->assertEquals(0, $stats['active_connections']);
        $this->assertEquals(5, $stats['idle_connections']);
    }

    /**
     * 测试连接池配置
     */
    public function testPoolConfig(): void
    {
        $config = new PoolConfig();
        $config->maxConnections = 100;
        $config->maxIdleTime = 120;
        $config->waitTimeout = 5.0;

        $pool = ConnectionPool::getInstance($config);

        $poolConfig = $pool->getConfig();
        $this->assertEquals(100, $poolConfig->maxConnections);
        $this->assertEquals(120, $poolConfig->maxIdleTime);
        $this->assertEquals(5.0, $poolConfig->waitTimeout);
    }
}
