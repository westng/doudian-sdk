<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Tests\Api\Product;

use DouDianSdk\Core\Client\DouDianSdk;
use DouDianSdk\Core\Exception\ApiException;
use DouDianSdk\Core\Exception\DouDianException;
use DouDianSdk\Core\Exception\HttpException;
use DouDianSdk\Tests\Support\ProductPublishTestHelper;
use DouDianSdk\Tests\TestCase;

use const DouDianSdk\Core\Token\ACCESS_TOKEN_SHOP_ID;

/**
 * product.GetRecommendCategory（文档: 14/2004）接口测试.
 */
class ProductGetRecommendCategoryApiTest extends TestCase
{
    private const PRODUCT_ADD_V2_PAYLOAD_ENV      = 'DOUDIAN_PRODUCT_ADD_V2_PAYLOAD_JSON';
    private const PRODUCT_ADD_V2_PAYLOAD_FILE_ENV = 'DOUDIAN_PRODUCT_ADD_V2_PAYLOAD_FILE';
    private const PRODUCT_RECOMMEND_CATEGORY_NAME_ENV = 'DOUDIAN_GET_RECOMMEND_CATEGORY_NAME';
    private const DEFAULT_PRODUCT_ADD_V2_PAYLOAD_FILE = 'tests/fixtures/product_add_v2_payload.example.json';
    private const DEFAULT_RECOMMEND_CATEGORY_PAYLOAD_FILE = 'tests/fixtures/product_get_recommend_category_payload.example.json';

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
     * 测试获取推荐类目.
     */
    public function testGetRecommendCategory(): void
    {
        $this->skipIntegrationTest();

        echo "\n=== 测试 product.GetRecommendCategory ===\n";

        try {
            $productPayload = ProductPublishTestHelper::loadJsonPayload(
                self::DEFAULT_PRODUCT_ADD_V2_PAYLOAD_FILE,
                (string) ($_ENV[self::PRODUCT_ADD_V2_PAYLOAD_ENV] ?? ''),
                (string) ($_ENV[self::PRODUCT_ADD_V2_PAYLOAD_FILE_ENV] ?? '')
            );
            $recommendPayload = ProductPublishTestHelper::loadJsonPayload(self::DEFAULT_RECOMMEND_CATEGORY_PAYLOAD_FILE);
            $requestData = ProductPublishTestHelper::buildRecommendCategoryPayload(
                $recommendPayload,
                $productPayload,
                $this->resolveProductName($productPayload)
            );

            echo "请求参数:\n";
            echo '  - param_json: ' . json_encode($requestData, JSON_UNESCAPED_UNICODE) . "\n";

            $result = $this->sdk->callApi(
                'product_GetRecommendCategory\\ProductGetRecommendCategoryRequest',
                'product_GetRecommendCategory\\param\\ProductGetRecommendCategoryParam',
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

            if (null === $statusCode && null === $statusMsg) {
                echo '  - 响应keys: ' . implode(', ', array_keys($result)) . "\n";
                echo '  - 响应体: ' . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n";
            }

            if (isset($result['err_no'])) {
                $this->assertEquals(0, (int) $result['err_no'], 'API should return success');
            } elseif (isset($result['code'])) {
                if (10000 !== (int) $result['code']) {
                    $this->outputWarning(
                        'API returned business error: code=' . $result['code'] . ', msg=' . ($result['msg'] ?? '')
                    );
                    return;
                }
            } else {
                $this->outputWarning('API返回中缺少状态码字段（err_no/code）');
                return;
            }

            echo "✅ 推荐类目API调用成功\n";

            if (isset($result['data'])) {
                echo '  - data: ' . json_encode($result['data'], JSON_UNESCAPED_UNICODE) . "\n";
            }
        } catch (ApiException $e) {
            echo '⚠️ API错误: ' . $e->getMessage() . "\n";
            echo '  - 错误码: ' . $e->getApiErrorCode() . "\n";
            echo '  - 日志ID: ' . $e->getLogId() . "\n";
            $this->outputWarning('API returned business error: ' . $e->getMessage());
        } catch (DouDianException $e) {
            echo '⚠️ SDK错误: ' . $e->getMessage() . "\n";
            $this->outputWarning('SDK returned error: ' . $e->getMessage());
        } catch (HttpException $e) {
            echo '❌ HTTP错误: ' . $e->getMessage() . "\n";
            echo '  - 状态码: ' . $e->getHttpStatusCode() . "\n";
            $this->fail('HTTP请求失败: ' . $e->getMessage());
        }
    }

    private function resolveProductName(array $productPayload): string
    {
        if (!empty($productPayload['name'])) {
            return trim((string) $productPayload['name']);
        }

        $productName = trim((string) ($_ENV[self::PRODUCT_RECOMMEND_CATEGORY_NAME_ENV] ?? '每日博士C3益生菌官方旗舰店正品冻干B420zb'));

        if ('' === $productName) {
            return '每日博士C3益生菌官方旗舰店正品冻干B420zb';
        }

        return $productName;
    }
}
