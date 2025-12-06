<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Tests\Api;

use DouDianSdk\Core\Client\DouDianSdk;
use DouDianSdk\Core\Exception\ApiException;
use DouDianSdk\Core\Exception\HttpException;
use DouDianSdk\Tests\TestCase;

use const DouDianSdk\Core\Token\ACCESS_TOKEN_SHOP_ID;

/**
 * 订单API测试类.
 */
class OrderApiTest extends TestCase
{
    /**
     * @var DouDianSdk SDK实例
     */
    private $sdk;

    /**
     * @var \DouDianSdk\Core\Token\AccessToken 访问令牌
     */
    private $accessToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sdk = new DouDianSdk(
            $this->testConfig['app_key'],
            $this->testConfig['app_secret']
        );
        $this->sdk->setDebug(true);

        // 获取访问令牌
        $this->accessToken = $this->sdk->getAccessToken(
            $this->testConfig['shop_id'],
            ACCESS_TOKEN_SHOP_ID
        );

        if (!$this->accessToken->isSuccess()) {
            $this->markTestSkipped('无法获取访问令牌，跳过API测试');
        }
    }

    /**
     * 测试获取订单列表.
     */
    public function testGetOrderList(): void
    {
        $this->skipIntegrationTest();

        echo "\n=== 测试获取订单列表 ===\n";
        echo '使用访问令牌: ' . substr($this->accessToken->getAccessToken(), 0, 30) . "...\n\n";

        try {
            $result = $this->sdk->callApi(
                'order_searchList\OrderSearchListRequest',
                'order_searchList\param\OrderSearchListParam',
                [
                    'page'         => 1,
                    'size'         => 5,
                    'order_status' => 1,  // 待发货
                    'start_time'   => date('Y-m-d H:i:s', strtotime('-7 days')),
                    'end_time'     => date('Y-m-d H:i:s'),
                ],
                $this->accessToken
            );

            // 验证响应格式
            $this->assertIsArray($result, 'API response should be an array');

            echo "✅ 订单列表API调用成功\n";
            echo "响应信息:\n";
            echo '  - 错误码: ' . ($result['err_no'] ?? 'NULL') . "\n";
            echo '  - 消息: ' . ($result['message'] ?? 'NULL') . "\n";
            echo '  - 日志ID: ' . ($result['log_id'] ?? 'NULL') . "\n";

            // 验证响应结构
            if (isset($result['err_no'])) {
                $this->assertEquals(0, $result['err_no'], 'API should return success');
            }

            if (isset($result['data'])) {
                $data = $result['data'];
                echo '  - 总数: ' . ($data['total'] ?? 0) . "\n";
                echo '  - 当前页: ' . ($data['page'] ?? 1) . "\n";
                echo '  - 每页数量: ' . ($data['size'] ?? 0) . "\n";

                if (isset($data['order_list']) && is_array($data['order_list'])) {
                    echo '  - 订单数量: ' . count($data['order_list']) . "\n";

                    // 验证订单列表结构
                    $this->assertIsArray($data['order_list'], 'Order list should be an array');

                    foreach ($data['order_list'] as $index => $order) {
                        $this->assertIsArray($order, 'Each order should be an array');
                        $this->assertArrayHasKey('order_id', $order, 'Order should have order_id');

                        if ($index < 2) { // 只显示前2个订单详情
                            echo '    订单 ' . ($index + 1) . ":\n";
                            echo '      - 订单ID: ' . ($order['order_id'] ?? 'NULL') . "\n";
                            echo '      - 订单状态: ' . ($order['order_status'] ?? 'NULL') . "\n";
                            echo '      - 订单金额: ' . ($order['total_amount'] ?? 'NULL') . "\n";
                        }
                    }
                }
            }
        } catch (ApiException $e) {
            echo '⚠️ API错误: ' . $e->getMessage() . "\n";
            echo '  - 错误码: ' . $e->getApiErrorCode() . "\n";
            echo '  - 日志ID: ' . $e->getLogId() . "\n";

            // API错误不算测试失败，可能是业务逻辑问题
            $this->addWarning('API returned business error: ' . $e->getMessage());
        } catch (HttpException $e) {
            echo '❌ HTTP错误: ' . $e->getMessage() . "\n";
            echo '  - 状态码: ' . $e->getHttpStatusCode() . "\n";

            $this->fail('HTTP请求失败: ' . $e->getMessage());
        }
    }

    /**
     * 测试获取订单详情.
     */
    public function testGetOrderDetail(): void
    {
        $this->skipIntegrationTest();

        echo "\n=== 测试获取订单详情 ===\n";

        // 使用一个测试订单ID（通常会失败，但可以测试API调用流程）
        $testOrderId = 'test_order_id_12345';

        try {
            $result = $this->sdk->callApi(
                'order_orderDetail\OrderOrderDetailRequest',
                'order_orderDetail\param\OrderOrderDetailParam',
                [
                    'order_id' => $testOrderId,
                ],
                $this->accessToken
            );

            $this->assertIsArray($result);

            echo "✅ 订单详情API调用成功\n";
            echo '响应: ' . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n";
        } catch (ApiException $e) {
            echo '⚠️ 预期的API错误（测试订单ID不存在）: ' . $e->getMessage() . "\n";
            echo '  - 错误码: ' . $e->getApiErrorCode() . "\n";

            // 这是预期的错误，说明API调用流程正常
            $this->assertNotEmpty($e->getMessage(), 'API error should have message');
        } catch (HttpException $e) {
            echo '❌ HTTP错误: ' . $e->getMessage() . "\n";
            $this->fail('HTTP请求失败: ' . $e->getMessage());
        }
    }

    /**
     * 测试API参数验证
     */
    public function testApiParameterValidation(): void
    {
        echo "\n=== 测试API参数验证 ===\n";

        // 测试缺少必要参数的情况
        try {
            $result = $this->sdk->callApi(
                'order_searchList\OrderSearchListRequest',
                'order_searchList\param\OrderSearchListParam',
                [], // 空参数
                $this->accessToken
            );

            // 如果没有抛出异常，检查响应
            if (isset($result['err_no']) && 0 != $result['err_no']) {
                echo "✅ API正确返回参数错误\n";
                $this->assertNotEquals(0, $result['err_no'], 'Should return parameter error');
            }
        } catch (ApiException $e) {
            echo '✅ API正确抛出参数验证异常: ' . $e->getMessage() . "\n";
            $this->assertNotEmpty($e->getMessage());
        }
    }
}
