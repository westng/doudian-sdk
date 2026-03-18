<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Tests\Api\Shop;

use DouDianSdk\Core\Client\DouDianSdk;
use DouDianSdk\Core\Exception\ApiException;
use DouDianSdk\Core\Exception\DouDianException;
use DouDianSdk\Tests\Support\ProductPublishTestHelper;
use DouDianSdk\Tests\TestCase;

use const DouDianSdk\Core\Token\ACCESS_TOKEN_SHOP_ID;

/**
 * shop.getShopCategory 接口测试.
 */
class ShopGetShopCategoryApiTest extends TestCase
{
    /**
     * @var DouDianSdk SDK实例
     */
    private $sdk;

    /**
     * @var \DouDianSdk\Core\Token\AccessToken|string 访问令牌
     */
    private $accessToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sdk = new DouDianSdk(
            $this->testConfig['app_key'],
            $this->testConfig['app_secret']
        );
        $this->sdk->setDebug(false);

        if (!empty($this->testConfig['access_token'])) {
            $this->accessToken = $this->testConfig['access_token'];
        } else {
            $this->accessToken = $this->sdk->getAccessToken(
                $this->testConfig['shop_id'],
                ACCESS_TOKEN_SHOP_ID
            );

            if (!$this->accessToken->isSuccess()) {
                $this->markTestSkipped('无法获取访问令牌，跳过API测试');
            }
        }
    }

    /**
     * 测试获取店铺类目.
     */
    public function testGetShopCategory(): void
    {
        $this->skipIntegrationTest();

        echo "\n=== 测试获取店铺类目 ===\n";

        try {
            $requestData = [
                'cid'     => 0,
                'channel' => 1,
            ];

            echo "请求参数:\n";
            echo '  - param_json: ' . json_encode($requestData, JSON_UNESCAPED_UNICODE) . "\n";

            $result = $this->sdk->callApi(
                'shop_getShopCategory\\ShopGetShopCategoryRequest',
                'shop_getShopCategory\\param\\ShopGetShopCategoryParam',
                $requestData,
                $this->accessToken
            );

            $this->assertIsArray($result, 'API response should be an array');

            echo "响应信息:\n";
            $statusCode = ProductPublishTestHelper::getApiCode($result);
            $statusMsg  = ProductPublishTestHelper::getApiMessage($result);
            echo '  - 错误码: ' . ($statusCode ?? 'NULL') . "\n";
            echo '  - 消息: ' . ($statusMsg ?? 'NULL') . "\n";
            echo '  - 日志ID: ' . ($result['log_id'] ?? 'NULL') . "\n";

            if (isset($result['err_no'])) {
                $this->assertEquals(0, (int) $result['err_no'], 'API should return success');
            } elseif (isset($result['code']) && 10000 !== (int) $result['code']) {
                $this->outputWarning(
                    'API returned business error: code=' . $result['code'] . ', msg=' . ($result['msg'] ?? '')
                );

                return;
            }

            echo "✅ 店铺类目API调用成功\n";

            $categories = $result['data']['data'] ?? [];

            if (is_array($categories)) {
                echo '  - 类目数量: ' . count($categories) . "\n";

                if (!empty($categories)) {
                    $this->assertArrayHasKey('id', $categories[0], 'Category should have id');
                    $this->assertArrayHasKey('name', $categories[0], 'Category should have name');
                }
            }
        } catch (ApiException $e) {
            echo '⚠️ API错误: ' . $e->getMessage() . "\n";
            echo '  - 错误码: ' . $e->getApiErrorCode() . "\n";
            echo '  - 日志ID: ' . $e->getLogId() . "\n";

            $this->outputWarning('API returned business error: ' . $e->getMessage());
        } catch (DouDianException $e) {
            echo '⚠️ SDK错误: ' . $e->getMessage() . "\n";
            $this->outputWarning('SDK returned error: ' . $e->getMessage());
        }
    }
}
