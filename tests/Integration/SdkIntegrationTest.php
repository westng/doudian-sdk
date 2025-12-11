<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Tests\Integration;

use DouDianSdk\Core\Client\DouDianSdk;
use DouDianSdk\Core\Exception\ApiException;
use DouDianSdk\Core\Exception\DouDianException;
use DouDianSdk\Core\Exception\HttpException;
use DouDianSdk\Core\Token\AccessTokenBuilder;
use DouDianSdk\Tests\TestCase;

/**
 * SDK集成测试类.
 *
 * 测试SDK的完整工作流程：获取Token -> 刷新Token -> 调用API
 */
class SdkIntegrationTest extends TestCase
{
    /**
     * @var DouDianSdk SDK实例
     */
    private $sdk;

    protected function setUp(): void
    {
        parent::setUp();

        // 触发常量加载
        class_exists(AccessTokenBuilder::class);

        $this->sdk = new DouDianSdk(
            $this->testConfig['app_key'],
            $this->testConfig['app_secret']
        );
        $this->sdk->setDebug(true);
    }

    /**
     * 测试完整的SDK工作流程.
     */
    public function testCompleteWorkflow(): void
    {
        $this->skipIntegrationTest();

        echo "\n=== SDK完整工作流程测试 ===\n";

        // 步骤1: 获取访问令牌
        echo "步骤1: 获取访问令牌...\n";
        $accessToken = $this->sdk->getAccessToken(
            $this->testConfig['shop_id'],
            $this->testConfig['access_token_shop_id']
        );

        $this->assertInstanceOf(
            \DouDianSdk\Core\Token\AccessToken::class,
            $accessToken
        );

        if (!$accessToken->isSuccess()) {
            $this->markTestSkipped('无法获取访问令牌，跳过完整流程测试');
        }

        echo "✅ 访问令牌获取成功\n";
        echo '  - Token: ' . substr($accessToken->getAccessToken(), 0, 30) . "...\n";
        echo '  - 有效期: ' . $accessToken->getExpireIn() . " 秒\n\n";

        // 步骤2: 测试刷新令牌（如果有）
        if ($accessToken->getRefreshToken()) {
            echo "步骤2: 测试刷新令牌...\n";

            try {
                $refreshedToken = $this->sdk->refreshAccessToken($accessToken->getRefreshToken());

                if ($refreshedToken->isSuccess()) {
                    echo "✅ 令牌刷新成功\n";
                    echo '  - 新Token: ' . substr($refreshedToken->getAccessToken(), 0, 30) . "...\n";
                    $accessToken = $refreshedToken; // 使用新令牌
                } else {
                    echo '⚠️ 令牌刷新失败: ' . $refreshedToken->getMessage() . "\n";
                }
            } catch (DouDianException $e) {
                echo '⚠️ 令牌刷新异常: ' . $e->getMessage() . "\n";
            }
        } else {
            echo "步骤2: 跳过令牌刷新（当前令牌类型不支持）\n";
        }

        // 步骤3: 调用多个API接口
        echo "\n步骤3: 调用API接口...\n";

        $apis = [
            [
                'name'  => '订单列表',
                'api'   => 'order_searchList\OrderSearchListRequest',
                'param' => 'order_searchList\param\OrderSearchListParam',
                'data'  => [
                    'page'         => 1,
                    'size'         => 2,
                    'order_status' => 1,
                    'start_time'   => date('Y-m-d H:i:s', strtotime('-3 days')),
                    'end_time'     => date('Y-m-d H:i:s'),
                ],
            ],
            [
                'name'  => '售后列表',
                'api'   => 'afterSale_List\AfterSaleListRequest',
                'param' => 'afterSale_List\param\AfterSaleListParam',
                'data'  => [
                    'page' => 1,
                    'size' => 2,
                ],
            ],
        ];

        $successCount = 0;

        foreach ($apis as $api) {
            try {
                echo "  - 调用 {$api['name']} API...\n";

                $result = $this->sdk->callApi(
                    $api['api'],
                    $api['param'],
                    $api['data'],
                    $accessToken
                );

                $this->assertIsArray($result);

                if (isset($result['err_no']) && 0 == $result['err_no']) {
                    echo "    ✅ {$api['name']} API调用成功\n";
                    ++$successCount;
                } else {
                    echo "    ⚠️ {$api['name']} API返回业务错误: " . ($result['message'] ?? 'Unknown') . "\n";
                }
            } catch (ApiException $e) {
                echo "    ⚠️ {$api['name']} API业务异常: " . $e->getMessage() . "\n";
            } catch (HttpException $e) {
                echo "    ❌ {$api['name']} HTTP异常: " . $e->getMessage() . "\n";
                $this->fail('HTTP请求失败: ' . $e->getMessage());
            }
        }

        echo "\n=== 工作流程测试完成 ===\n";
        echo "成功调用API数量: {$successCount}/" . count($apis) . "\n";

        // 至少要有一个API调用成功
        $this->assertGreaterThan(0, $successCount, 'At least one API call should succeed');
    }

    /**
     * 测试SDK配置.
     */
    public function testSdkConfiguration(): void
    {
        echo "\n=== 测试SDK配置 ===\n";

        // 测试超时配置
        $sdk = new DouDianSdk(
            $this->testConfig['app_key'],
            $this->testConfig['app_secret'],
            [
                'timeout' => [
                    'connect' => 8000,
                    'read'    => 15000,
                ],
                'retry' => [
                    'enable' => false,
                ],
                'debug' => true,
            ]
        );

        $config = $sdk->getConfig();

        echo "配置验证:\n";
        echo '  - 连接超时: ' . $config->httpConnectTimeout . " ms\n";
        echo '  - 读取超时: ' . $config->httpReadTimeout . " ms\n";
        echo '  - 重试启用: ' . ($config->enableRetry ? 'true' : 'false') . "\n";
        echo '  - 调试模式: ' . ($config->debug ? 'true' : 'false') . "\n";

        $this->assertEquals(8000, $config->httpConnectTimeout);
        $this->assertEquals(15000, $config->httpReadTimeout);
        $this->assertFalse($config->enableRetry);
        $this->assertTrue($config->debug);

        echo "✅ SDK配置测试通过\n";
    }

    /**
     * 测试错误处理.
     */
    public function testErrorHandling(): void
    {
        echo "\n=== 测试错误处理 ===\n";

        // 测试无效凭证
        $invalidSdk = new DouDianSdk('invalid_key', 'invalid_secret');

        try {
            $accessToken = $invalidSdk->getAccessToken('invalid_shop_id', $this->testConfig['access_token_shop_id']);

            if (!$accessToken->isSuccess()) {
                echo "✅ 无效凭证正确返回错误\n";
                echo '  - 错误码: ' . $accessToken->getErrNo() . "\n";
                echo '  - 错误消息: ' . $accessToken->getMessage() . "\n";

                $this->assertNotEquals(0, $accessToken->getErrNo());
                $this->assertNotEmpty($accessToken->getMessage());
            }
        } catch (DouDianException $e) {
            echo '✅ 无效凭证正确抛出异常: ' . $e->getMessage() . "\n";
            $this->assertNotEmpty($e->getMessage());
        }

        // 测试无效刷新令牌
        try {
            $invalidRefreshResult = $this->sdk->refreshAccessToken('invalid_refresh_token');

            if (!$invalidRefreshResult->isSuccess()) {
                echo "✅ 无效刷新令牌正确返回错误\n";
                $this->assertNotEquals(0, $invalidRefreshResult->getErrNo());
            }
        } catch (DouDianException $e) {
            echo '✅ 无效刷新令牌正确抛出异常: ' . $e->getMessage() . "\n";
            $this->assertNotEmpty($e->getMessage());
        }
    }
}
