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
use PHPUnit\Framework\TestCase;

/**
 * 配置测试类.
 */
class ConfigTest extends TestCase
{
    /**
     * 测试SDK基本配置.
     */
    public function testSdkBasicConfig(): void
    {
        echo "\n=== 测试SDK基本配置 ===\n";

        $sdk    = new DouDianSdk('test_key', 'test_secret');
        $config = $sdk->getConfig();

        echo "默认配置:\n";
        echo '  - 连接超时: ' . $config->httpConnectTimeout . " ms\n";
        echo '  - 读取超时: ' . $config->httpReadTimeout . " ms\n";
        echo '  - API版本: ' . $config->apiVersion . "\n";
        echo '  - 签名方法: ' . $config->signMethod . "\n";

        $this->assertEquals(5000, $config->httpConnectTimeout);
        $this->assertEquals(10000, $config->httpReadTimeout);
        $this->assertEquals(2, $config->apiVersion);
        $this->assertEquals('hmac-sha256', $config->signMethod);

        echo "✅ 基本配置测试通过\n";
    }

    /**
     * 测试自定义配置.
     */
    public function testCustomConfig(): void
    {
        echo "\n=== 测试自定义配置 ===\n";

        $sdk = new DouDianSdk('test_key', 'test_secret', [
            'timeout' => [
                'connect' => 8000,
                'read'    => 15000,
            ],
            'debug' => true,
            'retry' => [
                'enable' => false,
            ],
        ]);

        $config = $sdk->getConfig();

        echo "自定义配置:\n";
        echo '  - 连接超时: ' . $config->httpConnectTimeout . " ms\n";
        echo '  - 读取超时: ' . $config->httpReadTimeout . " ms\n";
        echo '  - 调试模式: ' . ($config->debug ? 'true' : 'false') . "\n";
        echo '  - 重试启用: ' . ($config->enableRetry ? 'true' : 'false') . "\n";

        $this->assertEquals(8000, $config->httpConnectTimeout);
        $this->assertEquals(15000, $config->httpReadTimeout);
        $this->assertTrue($config->debug);
        $this->assertFalse($config->enableRetry);

        echo "✅ 自定义配置测试通过\n";
    }
}
