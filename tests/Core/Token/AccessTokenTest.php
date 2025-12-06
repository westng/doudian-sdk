<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Tests\Core\Token;

use DouDianSdk\Core\Client\DouDianSdk;
use DouDianSdk\Core\Token\AccessToken;
use DouDianSdk\Core\Token\AccessTokenBuilder;
use PHPUnit\Framework\TestCase;

/**
 * AccessToken 测试类.
 */
class AccessTokenTest extends TestCase
{
    /**
     * @var DouDianSdk SDK实例
     */
    private $sdk;

    /**
     * @var array 测试配置
     */
    private $testConfig;

    protected function setUp(): void
    {
        parent::setUp();

        // 加载环境变量
        $this->loadEnvFile();

        $this->testConfig = [
            'app_key'       => $_ENV['DOUDIAN_APP_KEY'] ?? 'test_app_key',
            'app_secret'    => $_ENV['DOUDIAN_APP_SECRET'] ?? 'test_app_secret',
            'shop_id'       => $_ENV['DOUDIAN_SHOP_ID'] ?? 'test_shop_id',
            'refresh_token' => $_ENV['DOUDIAN_REFRESH_TOKEN'] ?? '',
        ];

        $this->sdk = new DouDianSdk(
            $this->testConfig['app_key'],
            $this->testConfig['app_secret']
        );
        $this->sdk->setDebug(true);
    }

    /**
     * 加载环境变量文件.
     */
    private function loadEnvFile(): void
    {
        $envFile = __DIR__ . '/../../../.env';

        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line) || 0 === strpos($line, '#')) {
                continue;
            }

            if (false !== strpos($line, '=')) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)]  = trim($value, '"\'');
            }
        }
    }

    /**
     * 检查是否启用集成测试.
     */
    private function isIntegrationTestEnabled(): bool
    {
        return !empty($_ENV['DOUDIAN_INTEGRATION_TEST'])
               && 'true' === $_ENV['DOUDIAN_INTEGRATION_TEST'];
    }

    /**
     * 测试通过店铺ID获取访问令牌.
     */
    public function testGetAccessTokenByShopId(): void
    {
        if (!$this->isIntegrationTestEnabled()) {
            $this->markTestSkipped('集成测试未启用');
        }

        echo "\n=== 测试获取访问令牌（通过店铺ID） ===\n";
        echo "使用配置:\n";
        echo '  - App Key: ' . $this->testConfig['app_key'] . "\n";
        echo '  - Shop ID: ' . $this->testConfig['shop_id'] . "\n\n";

        $accessToken = $this->sdk->getAccessToken(
            $this->testConfig['shop_id'],
            2  // ACCESS_TOKEN_SHOP_ID
        );

        // 验证返回对象类型
        $this->assertInstanceOf(
            AccessToken::class,
            $accessToken,
            'Expected AccessToken instance'
        );

        // 显示调试信息
        echo "调试信息:\n";
        echo '  - 错误码: ' . $accessToken->getErrNo() . "\n";
        echo '  - 消息: ' . $accessToken->getMessage() . "\n";
        echo '  - 日志ID: ' . $accessToken->getLogId() . "\n";

        if ($accessToken->isSuccess()) {
            echo "✅ 访问令牌获取成功\n";
            echo '  - Token: ' . substr($accessToken->getAccessToken() ?? '', 0, 30) . "...\n";
            echo '  - 有效期: ' . ($accessToken->getExpireIn() ?? 'NULL') . " 秒\n";
            echo '  - 店铺ID: ' . ($accessToken->getShopId() ?? 'NULL') . "\n";
            echo '  - 店铺名称: ' . ($accessToken->getShopName() ?? 'NULL') . "\n";

            // 验证必要字段
            $this->assertNotEmpty($accessToken->getAccessToken(), 'Access token should not be empty');
            $this->assertNotNull($accessToken->getExpireIn(), 'Expire time should not be null');
        } else {
            echo "❌ 访问令牌获取失败\n";
            echo '  - 错误码: ' . $accessToken->getErrNo() . "\n";
            echo '  - 错误消息: ' . $accessToken->getMessage() . "\n";

            $this->fail('获取访问令牌失败: ' . $accessToken->getMessage());
        }
    }

    /**
     * 测试刷新访问令牌.
     */
    public function testRefreshAccessToken(): void
    {
        if (!$this->isIntegrationTestEnabled()) {
            $this->markTestSkipped('集成测试未启用');
        }

        if (empty($this->testConfig['refresh_token'])) {
            $this->markTestSkipped('未配置刷新令牌，跳过此测试');
        }

        echo "\n=== 测试刷新访问令牌 ===\n";
        echo '刷新令牌: ' . substr($this->testConfig['refresh_token'], 0, 30) . "...\n\n";

        $newAccessToken = $this->sdk->refreshAccessToken($this->testConfig['refresh_token']);

        // 验证返回对象类型
        $this->assertInstanceOf(
            AccessToken::class,
            $newAccessToken,
            'Expected AccessToken instance'
        );

        echo "调试信息:\n";
        echo '  - 错误码: ' . $newAccessToken->getErrNo() . "\n";
        echo '  - 消息: ' . $newAccessToken->getMessage() . "\n";
        echo '  - 日志ID: ' . $newAccessToken->getLogId() . "\n";

        if ($newAccessToken->isSuccess()) {
            echo "✅ 刷新访问令牌成功\n";
            echo '  - 新Token: ' . substr($newAccessToken->getAccessToken() ?? '', 0, 30) . "...\n";
            echo '  - 有效期: ' . ($newAccessToken->getExpireIn() ?? 'NULL') . " 秒\n";
            echo '  - 店铺ID: ' . ($newAccessToken->getShopId() ?? 'NULL') . "\n";

            // 验证必要字段
            $this->assertNotEmpty($newAccessToken->getAccessToken(), 'New access token should not be empty');
            $this->assertNotNull($newAccessToken->getExpireIn(), 'Expire time should not be null');
        } else {
            echo "❌ 刷新访问令牌失败\n";
            echo '  - 错误码: ' . $newAccessToken->getErrNo() . "\n";
            echo '  - 错误消息: ' . $newAccessToken->getMessage() . "\n";

            $this->fail('刷新访问令牌失败: ' . $newAccessToken->getMessage());
        }
    }

    /**
     * 测试AccessTokenBuilder直接调用.
     */
    public function testAccessTokenBuilderDirect(): void
    {
        if (!$this->isIntegrationTestEnabled()) {
            $this->markTestSkipped('集成测试未启用');
        }

        echo "\n=== 测试AccessTokenBuilder直接调用 ===\n";

        $accessToken = AccessTokenBuilder::build(
            $this->testConfig['shop_id'],
            2  // ACCESS_TOKEN_SHOP_ID
        );

        $this->assertInstanceOf(
            AccessToken::class,
            $accessToken
        );

        if ($accessToken->isSuccess()) {
            echo "✅ AccessTokenBuilder直接调用成功\n";
            $this->assertNotEmpty($accessToken->getAccessToken());
        } else {
            echo '❌ AccessTokenBuilder直接调用失败: ' . $accessToken->getMessage() . "\n";
        }
    }
}
