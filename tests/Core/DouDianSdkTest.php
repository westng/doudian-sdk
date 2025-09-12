<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Tests\Core;

use DouDianSdk\Core\Client\DouDianSdk;
use DouDianSdk\Core\Logger\NullLogger;
use DouDianSdk\Tests\TestCase;

class DouDianSdkTest extends TestCase
{
    /**
     * @var DouDianSdk SDK实例
     */
    private $sdk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sdk = new DouDianSdk('test_app_key', 'test_app_secret');
    }

    public function testConstructor(): void
    {
        $sdk = new DouDianSdk('test_key', 'test_secret');

        $this->assertInstanceOf(DouDianSdk::class, $sdk);
        $this->assertEquals('test_key', $sdk->getConfig()->appKey);
        $this->assertEquals('test_secret', $sdk->getConfig()->appSecret);
    }

    public function testSetCredentials(): void
    {
        $this->sdk->setCredentials('new_key', 'new_secret');

        $this->assertEquals('new_key', $this->sdk->getConfig()->appKey);
        $this->assertEquals('new_secret', $this->sdk->getConfig()->appSecret);
    }

    public function testSetDebug(): void
    {
        $this->sdk->setDebug(true);

        $this->assertTrue($this->sdk->getConfig()->debug);

        $this->sdk->setDebug(false);

        $this->assertFalse($this->sdk->getConfig()->debug);
    }

    public function testSetLogger(): void
    {
        $logger = new NullLogger();
        $this->sdk->setLogger($logger);

        $this->assertSame($logger, $this->sdk->getLogger());
    }

    public function testGetConfig(): void
    {
        $config = $this->sdk->getConfig();

        $this->assertInstanceOf(\DouDianSdk\Core\Config\GlobalConfig::class, $config);
    }

    public function testGetLogger(): void
    {
        $logger = $this->sdk->getLogger();

        $this->assertInstanceOf(\DouDianSdk\Core\Logger\LoggerInterface::class, $logger);
    }
}
