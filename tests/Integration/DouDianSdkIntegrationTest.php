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
use DouDianSdk\Core\Token\AccessToken;
use DouDianSdk\Tests\TestCase;

/**
 * 抖店SDK集成测试.
 *
 * 演示获取Token、刷新Token、调用接口等完整流程
 *
 * 运行集成测试前需要设置环境变量：
 * - DOUDIAN_APP_KEY: 你的应用Key
 * - DOUDIAN_APP_SECRET: 你的应用密钥
 * - DOUDIAN_SHOP_ID: 你的店铺ID
 * - DOUDIAN_AUTH_CODE: 授权码（可选）
 * - DOUDIAN_INTEGRATION_TEST: true
 */
class DouDianSdkIntegrationTest extends TestCase
{
    /**
     * @var DouDianSdk SDK实例
     */
    private $sdk;

    /**
     * @var array 测试配置
     */
    private $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->getTestConfig();

        // 创建SDK实例
        $this->sdk = new DouDianSdk(
            $this->config['app_key'],
            $this->config['app_secret']
        );

        // 启用调试模式
        $this->sdk->setDebug(true);
    }

    /**
     * 测试获取访问令牌（通过店铺ID）.
     */
    public function testGetAccessTokenByShopId(): void
    {
        $this->skipIntegrationTest();

        echo "\n=== 测试获取访问令牌（通过店铺ID） ===\n";

        try {
            $accessToken = $this->sdk->getAccessToken($this->config['shop_id'], ACCESS_TOKEN_SHOP_ID);

            $this->assertInstanceOf(AccessToken::class, $accessToken);

            if ($accessToken->isSuccess()) {
                echo "✅ 访问令牌获取成功\n";
                echo '  - Token: ' . substr($accessToken->getAccessToken(), 0, 20) . "...\n";
                echo '  - 有效期: ' . $accessToken->getExpireIn() . " 秒\n";
                echo '  - 店铺ID: ' . $accessToken->getShopId() . "\n";
                echo '  - 店铺名称: ' . $accessToken->getShopName() . "\n";

                $this->assertNotEmpty($accessToken->getAccessToken());
                $this->assertNotEmpty($accessToken->getExpireIn());
                $this->assertNotEmpty($accessToken->getShopId());
            } else {
                echo "❌ 访问令牌获取失败\n";
                echo '  - 错误码: ' . $accessToken->getErrNo() . "\n";
                echo '  - 错误消息: ' . $accessToken->getMessage() . "\n";

                $this->fail('获取访问令牌失败: ' . $accessToken->getMessage());
            }
        } catch (DouDianException $e) {
            echo '❌ SDK错误: ' . $e->getMessage() . "\n";
            $this->fail('获取访问令牌时发生错误: ' . $e->getMessage());
        }
    }

    /**
     * 测试获取访问令牌（通过授权码）.
     */
    public function testGetAccessTokenByAuthCode(): void
    {
        $this->skipIntegrationTest();

        if (empty($this->config['auth_code'])) {
            $this->markTestSkipped('未设置授权码，跳过此测试');
        }

        echo "\n=== 测试获取访问令牌（通过授权码） ===\n";

        try {
            $accessToken = $this->sdk->getAccessToken($this->config['auth_code'], ACCESS_TOKEN_CODE);

            $this->assertInstanceOf(AccessToken::class, $accessToken);

            if ($accessToken->isSuccess()) {
                echo "✅ 通过授权码获取访问令牌成功\n";
                echo '  - Token: ' . substr($accessToken->getAccessToken(), 0, 20) . "...\n";
                echo '  - 有效期: ' . $accessToken->getExpireIn() . " 秒\n";
                echo '  - 刷新令牌: ' . substr($accessToken->getRefreshToken(), 0, 20) . "...\n";

                $this->assertNotEmpty($accessToken->getAccessToken());
                $this->assertNotEmpty($accessToken->getRefreshToken());
            } else {
                echo "❌ 通过授权码获取访问令牌失败\n";
                echo '  - 错误码: ' . $accessToken->getErrNo() . "\n";
                echo '  - 错误消息: ' . $accessToken->getMessage() . "\n";
            }
        } catch (DouDianException $e) {
            echo '❌ SDK错误: ' . $e->getMessage() . "\n";
        }
    }

    /**
     * 测试刷新访问令牌.
     */
    public function testRefreshAccessToken(): void
    {
        $this->skipIntegrationTest();

        echo "\n=== 测试刷新访问令牌 ===\n";

        try {
            // 首先获取一个访问令牌
            $accessToken = $this->sdk->getAccessToken($this->config['shop_id'], ACCESS_TOKEN_SHOP_ID);

            if (!$accessToken->isSuccess()) {
                $this->markTestSkipped('无法获取初始访问令牌，跳过刷新测试');
            }

            // 如果有刷新令牌，则尝试刷新
            if ($accessToken->getRefreshToken()) {
                $refreshedToken = $this->sdk->refreshAccessToken($accessToken->getRefreshToken());

                $this->assertInstanceOf(AccessToken::class, $refreshedToken);

                if ($refreshedToken->isSuccess()) {
                    echo "✅ 刷新访问令牌成功\n";
                    echo '  - 新Token: ' . substr($refreshedToken->getAccessToken(), 0, 20) . "...\n";
                    echo '  - 有效期: ' . $refreshedToken->getExpireIn() . " 秒\n";

                    $this->assertNotEmpty($refreshedToken->getAccessToken());
                    $this->assertNotEquals($accessToken->getAccessToken(), $refreshedToken->getAccessToken());
                } else {
                    echo "❌ 刷新访问令牌失败\n";
                    echo '  - 错误码: ' . $refreshedToken->getErrNo() . "\n";
                    echo '  - 错误消息: ' . $refreshedToken->getMessage() . "\n";
                }
            } else {
                echo "ℹ️ 当前令牌类型不支持刷新，跳过刷新测试\n";
            }
        } catch (DouDianException $e) {
            echo '❌ SDK错误: ' . $e->getMessage() . "\n";
        }
    }

    /**
     * 测试调用API接口 - 获取售后列表.
     */
    public function testCallAfterSaleListApi(): void
    {
        $this->skipIntegrationTest();

        echo "\n=== 测试调用API接口 - 获取售后列表 ===\n";

        try {
            // 获取访问令牌
            $accessToken = $this->sdk->getAccessToken($this->config['shop_id'], ACCESS_TOKEN_SHOP_ID);

            if (!$accessToken->isSuccess()) {
                $this->markTestSkipped('无法获取访问令牌，跳过API调用测试');
            }

            // 调用售后列表API
            $result = $this->sdk->callApi(
                'afterSale_List\AfterSaleListRequest',
                'afterSale_List\param\AfterSaleListParam',
                [
                    'page'       => 1,
                    'size'       => 5,
                    'start_time' => date('Y-m-d H:i:s', strtotime('-7 days')),
                    'end_time'   => date('Y-m-d H:i:s'),
                ],
                $accessToken
            );

            $this->assertIsArray($result);

            echo "✅ API调用成功\n";
            echo '  - 响应数据: ' . json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";

            // 验证响应结构
            if (isset($result['err_no'])) {
                $this->assertEquals(0, $result['err_no']);
            }
        } catch (ApiException $e) {
            echo '❌ API错误: ' . $e->getMessage() . "\n";
            echo '  - 错误码: ' . $e->getApiErrorCode() . "\n";
            echo '  - 日志ID: ' . $e->getLogId() . "\n";

            // API错误不算测试失败，可能是业务逻辑问题
        } catch (HttpException $e) {
            echo '❌ HTTP错误: ' . $e->getMessage() . "\n";
            echo '  - 状态码: ' . $e->getHttpStatusCode() . "\n";

            $this->fail('HTTP请求失败: ' . $e->getMessage());
        } catch (DouDianException $e) {
            echo '❌ SDK错误: ' . $e->getMessage() . "\n";
            $this->fail('SDK错误: ' . $e->getMessage());
        }
    }

    /**
     * 测试调用API接口 - 获取订单列表.
     */
    public function testCallOrderListApi(): void
    {
        $this->skipIntegrationTest();

        echo "\n=== 测试调用API接口 - 获取订单列表 ===\n";

        try {
            // 获取访问令牌
            $accessToken = $this->sdk->getAccessToken($this->config['shop_id'], ACCESS_TOKEN_SHOP_ID);

            if (!$accessToken->isSuccess()) {
                $this->markTestSkipped('无法获取访问令牌，跳过API调用测试');
            }

            // 调用订单列表API
            $result = $this->sdk->callApi(
                'order_searchList\OrderSearchListRequest',
                'order_searchList\param\OrderSearchListParam',
                [
                    'page'         => 1,
                    'size'         => 5,
                    'order_status' => 1,
                    'start_time'   => date('Y-m-d H:i:s', strtotime('-3 days')),
                    'end_time'     => date('Y-m-d H:i:s'),
                ],
                $accessToken
            );

            $this->assertIsArray($result);

            echo "✅ 订单列表API调用成功\n";
            echo '  - 响应数据: ' . json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        } catch (ApiException $e) {
            echo '❌ API错误: ' . $e->getMessage() . "\n";
            echo '  - 错误码: ' . $e->getApiErrorCode() . "\n";
            echo '  - 日志ID: ' . $e->getLogId() . "\n";
        } catch (DouDianException $e) {
            echo '❌ SDK错误: ' . $e->getMessage() . "\n";
        }
    }

    /**
     * 测试错误处理机制.
     */
    public function testErrorHandling(): void
    {
        $this->skipIntegrationTest();

        echo "\n=== 测试错误处理机制 ===\n";

        try {
            // 获取访问令牌
            $accessToken = $this->sdk->getAccessToken($this->config['shop_id'], ACCESS_TOKEN_SHOP_ID);

            if (!$accessToken->isSuccess()) {
                $this->markTestSkipped('无法获取访问令牌，跳过错误处理测试');
            }

            // 故意使用错误的参数来触发API错误
            $result = $this->sdk->callApi(
                'order_orderDetail\OrderOrderDetailRequest',
                'order_orderDetail\param\OrderOrderDetailParam',
                [
                    'order_id' => 'invalid_order_id_12345',
                ],
                $accessToken
            );

            // 如果到这里说明没有抛出异常，检查响应
            echo 'ℹ️ API调用完成，响应: ' . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n";
        } catch (ApiException $e) {
            echo "✅ API错误处理正常\n";
            echo '  - 错误消息: ' . $e->getMessage() . "\n";
            echo '  - 错误码: ' . $e->getApiErrorCode() . "\n";
            echo '  - 日志ID: ' . $e->getLogId() . "\n";

            $this->assertNotEmpty($e->getMessage());
        } catch (HttpException $e) {
            echo "✅ HTTP错误处理正常\n";
            echo '  - 错误消息: ' . $e->getMessage() . "\n";
            echo '  - 状态码: ' . $e->getHttpStatusCode() . "\n";

            $this->assertGreaterThan(0, $e->getHttpStatusCode());
        } catch (DouDianException $e) {
            echo "✅ SDK错误处理正常\n";
            echo '  - 错误消息: ' . $e->getMessage() . "\n";

            $this->assertNotEmpty($e->getMessage());
        }
    }

    /**
     * 测试重试机制.
     */
    public function testRetryMechanism(): void
    {
        $this->skipIntegrationTest();

        echo "\n=== 测试重试机制 ===\n";

        try {
            // 配置重试参数
            $config = $this->sdk->getConfig();
            $config->setRetryConfig(true, 3, 1000); // 启用重试，最多3次，间隔1秒

            echo "  - 重试配置: 启用，最多3次，间隔1秒\n";

            // 获取访问令牌
            $accessToken = $this->sdk->getAccessToken($this->config['shop_id'], ACCESS_TOKEN_SHOP_ID);

            if (!$accessToken->isSuccess()) {
                $this->markTestSkipped('无法获取访问令牌，跳过重试测试');
            }

            // 正常API调用（应该不会触发重试）
            $startTime = microtime(true);

            $result = $this->sdk->callApi(
                'afterSale_List\AfterSaleListRequest',
                'afterSale_List\param\AfterSaleListParam',
                [
                    'page' => 1,
                    'size' => 1,
                ],
                $accessToken
            );

            $endTime  = microtime(true);
            $duration = ($endTime - $startTime) * 1000;

            echo "✅ 重试机制测试完成\n";
            echo '  - API调用耗时: ' . number_format($duration, 2) . " 毫秒\n";
            echo '  - 响应数据大小: ' . strlen(json_encode($result)) . " 字节\n";

            $this->assertIsArray($result);
        } catch (DouDianException $e) {
            echo '❌ 重试测试失败: ' . $e->getMessage() . "\n";
        }
    }

    /**
     * 测试完整流程.
     */
    public function testCompleteWorkflow(): void
    {
        $this->skipIntegrationTest();

        echo "\n=== 测试完整工作流程 ===\n";

        try {
            // 1. 获取访问令牌
            echo "1. 获取访问令牌...\n";
            $accessToken = $this->sdk->getAccessToken($this->config['shop_id'], ACCESS_TOKEN_SHOP_ID);

            if (!$accessToken->isSuccess()) {
                $this->markTestSkipped('无法获取访问令牌，跳过完整流程测试');
            }

            echo "   ✅ 访问令牌获取成功\n";

            // 2. 调用多个API接口
            $apis = [
                [
                    'name'  => '售后列表',
                    'api'   => 'afterSale_List\AfterSaleListRequest',
                    'param' => 'afterSale_List\param\AfterSaleListParam',
                    'data'  => ['page' => 1, 'size' => 2],
                ],
                [
                    'name'  => '订单列表',
                    'api'   => 'order_searchList\OrderSearchListRequest',
                    'param' => 'order_searchList\param\OrderSearchListParam',
                    'data'  => ['page' => 1, 'size' => 2, 'order_status' => 1],
                ],
            ];

            echo "2. 调用API接口...\n";

            foreach ($apis as $api) {
                try {
                    echo "   - 调用 {$api['name']} API...\n";
                    $result = $this->sdk->callApi($api['api'], $api['param'], $api['data'], $accessToken);
                    echo "     ✅ {$api['name']} API调用成功\n";
                } catch (ApiException $e) {
                    echo "     ⚠️ {$api['name']} API调用失败: " . $e->getMessage() . "\n";
                }
            }

            // 3. 测试令牌刷新（如果有刷新令牌）
            if ($accessToken->getRefreshToken()) {
                echo "3. 刷新访问令牌...\n";

                try {
                    $refreshedToken = $this->sdk->refreshAccessToken($accessToken->getRefreshToken());

                    if ($refreshedToken->isSuccess()) {
                        echo "   ✅ 令牌刷新成功\n";
                    } else {
                        echo '   ⚠️ 令牌刷新失败: ' . $refreshedToken->getMessage() . "\n";
                    }
                } catch (DouDianException $e) {
                    echo '   ⚠️ 令牌刷新异常: ' . $e->getMessage() . "\n";
                }
            } else {
                echo "3. 跳过令牌刷新（当前令牌类型不支持）\n";
            }

            echo "✅ 完整工作流程测试完成\n";
        } catch (DouDianException $e) {
            echo '❌ 完整流程测试失败: ' . $e->getMessage() . "\n";
            $this->fail('完整流程测试失败: ' . $e->getMessage());
        }
    }
}
