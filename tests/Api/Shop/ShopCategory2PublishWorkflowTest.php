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
use DouDianSdk\Core\Exception\HttpException;
use DouDianSdk\Core\Token\AccessToken;
use DouDianSdk\Core\Token\AccessTokenBuilder;
use DouDianSdk\Tests\Support\ProductPublishTestHelper;
use DouDianSdk\Tests\TestCase;

use const DouDianSdk\Core\Token\ACCESS_TOKEN_SHOP_ID;

/**
 * 抖店2.0类目发布链路测试:
 * Step1 token校验 -> Step2 shop.getShopCategory(channel=1) -> Step3 product.GetRecommendCategory -> Step4 product.addV2.
 */
class ShopCategory2PublishWorkflowTest extends TestCase
{
    /**
     * 允许校验的店铺ID白名单.
     */
    private const ALLOWED_SHOP_IDS = [
        '260477060',
        '4463798',
        '264351976',
        '255085124',
    ];

    /**
     * 可选：完整 product.addV2 业务参数（JSON）环境变量名.
     */
    private const PRODUCT_ADD_V2_PAYLOAD_ENV              = 'DOUDIAN_PRODUCT_ADD_V2_PAYLOAD_JSON';
    private const PRODUCT_ADD_V2_PAYLOAD_FILE_ENV         = 'DOUDIAN_PRODUCT_ADD_V2_PAYLOAD_FILE';
    private const GET_RECOMMEND_CATEGORY_SCENE_ENV        = 'DOUDIAN_GET_RECOMMEND_CATEGORY_SCENE';
    private const DEFAULT_PRODUCT_ADD_V2_PAYLOAD_FILE     = 'tests/fixtures/product_add_v2_payload.example.json';
    private const DEFAULT_RECOMMEND_CATEGORY_PAYLOAD_FILE = 'tests/fixtures/product_get_recommend_category_payload.example.json';

    /**
     * @var DouDianSdk SDK实例
     */
    private $sdk;

    protected function setUp(): void
    {
        parent::setUp();

        class_exists(AccessTokenBuilder::class);

        $this->sdk = new DouDianSdk(
            $this->testConfig['app_key'],
            $this->testConfig['app_secret']
        );
        $this->sdk->setDebug(false);
    }

    /**
     * Step1-4 全链路验证.
     */
    public function testShopCategory2PublishWorkflow(): void
    {
        $this->skipIntegrationTest();

        echo "\n=== Step1: access_token 校验 ===\n";
        $accessToken = $this->fetchAccessTokenByShopId();
        $tokenShopId = (string) $accessToken->getShopId();
        $step1LogId  = (string) $accessToken->getLogId();

        echo 'Step1 logid: ' . ($step1LogId ?: 'NULL') . "\n";
        echo 'Token携带店铺ID: ' . ($tokenShopId ?: 'NULL') . "\n";
        $this->assertNotEmpty($step1LogId, 'Step1 must return logid');
        $this->assertContains(
            $tokenShopId,
            self::ALLOWED_SHOP_IDS,
            'access_token携带店铺ID不在平台指定白名单内'
        );

        echo "\n=== Step2: /shop/getShopCategory(channel=1) ===\n";
        $step2Channel = 1;
        $this->assertSame(1, $step2Channel, 'Step2请求参数channel必须为1');
        echo 'Step2 请求参数: ' . json_encode([
            'cid'     => 0,
            'channel' => $step2Channel,
        ], JSON_UNESCAPED_UNICODE) . "\n";

        $leafCategory   = $this->findLeafCategory($accessToken, $step2Channel);
        $leafCategoryId = $leafCategory['id'];
        $step2LogId     = $leafCategory['log_id'];

        echo 'Step2 logid: ' . ($step2LogId ?: 'NULL') . "\n";
        echo 'Step2 选中叶子类目ID: ' . $leafCategoryId . "\n";
        $this->assertNotEmpty($step2LogId, 'Step2 must return logid');
        $this->assertNotEmpty($leafCategoryId, 'Step2 must resolve a leaf category id');

        $payload = $this->buildProductAddV2Payload($leafCategoryId);
        $this->validateProductAddV2Payload($payload);

        echo "\n=== Step3: /product/GetRecommendCategory（提取recommend_id） ===\n";
        $recommendResult = $this->fetchRecommendIds($accessToken, $payload);
        $recommendIds    = $recommendResult['recommend_ids'];
        $step3LogId      = $recommendResult['log_id'];
        echo 'Step3 请求参数: ' . json_encode($recommendResult['request_data'], JSON_UNESCAPED_UNICODE) . "\n";
        echo 'Step3 logid: ' . ($step3LogId ?: 'NULL') . "\n";
        echo 'Step3 recommend_id(s): ' . json_encode($recommendIds, JSON_UNESCAPED_UNICODE) . "\n";

        if (empty($recommendIds)) {
            $this->markTestSkipped(
                'Step3未返回recommend_id，无法验证Step4携带recommend_id链路。logid=' . ($step3LogId ?: 'NULL')
            );
        }

        echo "\n=== Step4: /product/addV2（使用Step2类目 + Step3 recommend_ids） ===\n";
        $this->assertCount(1, $recommendIds, 'Step3当前应只返回一个recommend_id用于Step4透传');
        $payload['recommend_ids'] = [
            'category_recommend_id' => (string) $recommendIds[0],
        ];
        $this->assertSame(
            (string) $leafCategoryId,
            (string) ($payload['category_leaf_id'] ?? ''),
            'Step4必须使用Step2返回的类目创建商品'
        );
        $this->assertNotEmpty($payload['recommend_ids'] ?? [], 'Step4必须携带GetRecommendCategory返回的recommend_id');
        $this->assertSame(
            ProductPublishTestHelper::normalizeIdList($recommendIds),
            ProductPublishTestHelper::normalizeIdList(array_values($payload['recommend_ids'] ?? [])),
            'Step4中的recommend_ids必须和Step3返回一致'
        );
        echo 'Step4 请求参数: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n";
        echo 'Step4 提交 recommend_ids: ' . json_encode($payload['recommend_ids'], JSON_UNESCAPED_UNICODE) . "\n";

        try {
            $result = $this->sdk->callApi(
                'product_addV2\\ProductAddV2Request',
                'product_addV2\\param\\ProductAddV2Param',
                $payload,
                $accessToken
            );

            $this->assertIsArray($result, 'Step4 response should be an array');
            $step4LogId = (string) ($result['log_id'] ?? '');
            $step4Code  = ProductPublishTestHelper::getApiCode($result);
            $step4Msg   = ProductPublishTestHelper::getApiMessage($result);
            echo 'Step4 logid: ' . ($step4LogId ?: 'NULL') . "\n";
            echo 'Step4 错误码: ' . ($step4Code ?? 'NULL') . "\n";
            echo 'Step4 消息: ' . ($step4Msg ?: 'NULL') . "\n";

            $this->assertNotEmpty($step4LogId, 'Step4 must return logid');
            $this->assertTrue(
                ProductPublishTestHelper::isApiSuccess($result),
                'Step4商品创建并发布失败: code=' . ($step4Code ?? 'NULL') . ', msg=' . ($step4Msg ?: 'NULL')
            );
        } catch (ApiException $e) {
            $this->fail(
                'Step4业务失败，logid=' . $e->getLogId() . '，code=' . $e->getApiErrorCode() . '，msg=' . $e->getMessage()
            );
        } catch (HttpException $e) {
            $this->fail('Step4 HTTP失败: ' . $e->getMessage());
        } catch (DouDianException $e) {
            $this->fail('Step4 SDK失败: ' . $e->getMessage());
        }
    }

    private function fetchAccessTokenByShopId(): AccessToken
    {
        $token = $this->sdk->getAccessToken(
            $this->testConfig['shop_id'],
            ACCESS_TOKEN_SHOP_ID
        );

        $this->assertTrue($token->isSuccess(), 'Step1获取access_token失败: ' . $token->getMessage());

        return $token;
    }

    private function findLeafCategory(AccessToken $accessToken, int $channel): array
    {
        $cid = 0;

        for ($depth = 0; $depth < 10; ++$depth) {
            $result = $this->sdk->callApi(
                'shop_getShopCategory\\ShopGetShopCategoryRequest',
                'shop_getShopCategory\\param\\ShopGetShopCategoryParam',
                [
                    'cid'     => $cid,
                    'channel' => $channel,
                ],
                $accessToken
            );

            $logId = (string) ($result['log_id'] ?? '');
            $this->assertTrue(
                ProductPublishTestHelper::isApiSuccess($result),
                'Step2获取类目失败: code=' . (ProductPublishTestHelper::getApiCode($result) ?? 'NULL')
                . ', msg=' . ProductPublishTestHelper::getApiMessage($result)
            );

            $categories = $this->extractCategoryList($result);
            $this->assertIsArray($categories, 'Step2类目返回必须为数组');

            if (empty($categories)) {
                $this->markTestSkipped(
                    'Step2类目返回为空，无法继续Step3。logid=' . ($logId ?: 'NULL')
                );
            }

            foreach ($categories as $item) {
                if (!isset($item['id'])) {
                    continue;
                }

                if ($this->toBool($item['is_leaf'] ?? false) && $this->toBool($item['enable'] ?? true)) {
                    return [
                        'id'     => (string) $item['id'],
                        'log_id' => $logId,
                    ];
                }
            }

            $nextCid = null;

            foreach ($categories as $item) {
                if (!isset($item['id'])) {
                    continue;
                }

                if (!$this->toBool($item['is_leaf'] ?? false) && $this->toBool($item['enable'] ?? true)) {
                    $nextCid = (int) $item['id'];
                    break;
                }
            }

            if (null === $nextCid) {
                break;
            }

            $cid = $nextCid;
        }

        $this->markTestSkipped('Step2未找到可用叶子类目，无法继续Step3，请检查店铺类目权限');
    }

    private function buildProductAddV2Payload(string $leafCategoryId): array
    {
        $payload = ProductPublishTestHelper::loadJsonPayload(
            self::DEFAULT_PRODUCT_ADD_V2_PAYLOAD_FILE,
            (string) ($_ENV[self::PRODUCT_ADD_V2_PAYLOAD_ENV] ?? ''),
            (string) ($_ENV[self::PRODUCT_ADD_V2_PAYLOAD_FILE_ENV] ?? '')
        );

        if ([] === $payload) {
            $this->markTestSkipped(
                '缺少商品创建测试数据，请检查 ' . self::DEFAULT_PRODUCT_ADD_V2_PAYLOAD_FILE
            );
        }

        $payload['category_leaf_id'] = (int) $leafCategoryId;

        if (!array_key_exists('product_type', $payload)) {
            $payload['product_type'] = 0;
        }

        if (array_key_exists('product_format_new', $payload)) {
            if ([] === $payload['product_format_new'] || null === $payload['product_format_new']) {
                unset($payload['product_format_new']);
            } elseif (is_array($payload['product_format_new'])) {
                $payload['product_format_new'] = json_encode($payload['product_format_new'], JSON_UNESCAPED_UNICODE);
            }
        }

        if (isset($payload['pic'])) {
            $payload['pic'] = ProductPublishTestHelper::normalizeMediaString($payload['pic']);
        }

        if (isset($payload['description'])) {
            $payload['description'] = ProductPublishTestHelper::normalizeMediaString($payload['description']);
        }

        $suffix = '_' . date('YmdHis');

        if (!empty($payload['out_product_id']) && !is_numeric($payload['out_product_id'])) {
            if (empty($payload['outer_product_id'])) {
                $payload['outer_product_id'] = (string) $payload['out_product_id'];
            }
            unset($payload['out_product_id']);
        }

        if (!empty($payload['outer_product_id'])) {
            $payload['outer_product_id'] = (string) $payload['outer_product_id'] . $suffix;
        }

        if (!empty($payload['out_product_id'])) {
            $payload['out_product_id'] = (int) $payload['out_product_id'];
        }

        if (empty($payload['outer_product_id']) && empty($payload['out_product_id'])) {
            $payload['outer_product_id'] = 'autotest' . $suffix;
        }

        return $payload;
    }

    private function validateProductAddV2Payload(array $payload): void
    {
        $requiredFields = [
            'product_type',
            'name',
            'pic',
            'description',
            'reduce_type',
            'freight_id',
            'mobile',
            'commit',
            'category_leaf_id',
        ];

        $missing = [];

        foreach ($requiredFields as $field) {
            if (!$this->hasProvidedValue($payload, $field)) {
                $missing[] = $field;
            }
        }

        $this->assertSame(
            [],
            $missing,
            'Step4缺少必填参数: ' . implode(', ', $missing)
        );
    }

    private function hasProvidedValue(array $payload, string $field): bool
    {
        if (!array_key_exists($field, $payload)) {
            return false;
        }

        $value = $payload[$field];

        if (is_string($value)) {
            return '' !== trim($value);
        }

        return null !== $value;
    }

    private function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value > 0;
        }

        $str = strtolower((string) $value);

        return in_array($str, ['true', '1', 'yes', 'y'], true);
    }

    private function fetchRecommendIds(AccessToken $accessToken, array $productPayload): array
    {
        $recommendPayload = ProductPublishTestHelper::loadJsonPayload(self::DEFAULT_RECOMMEND_CATEGORY_PAYLOAD_FILE);
        $requestData = ProductPublishTestHelper::buildRecommendCategoryPayload(
            $recommendPayload,
            $productPayload,
            '自动化测试商品',
            (string) ($_ENV[self::GET_RECOMMEND_CATEGORY_SCENE_ENV] ?? '')
        );

        if ('predict_by_title_and_img' === ($requestData['scene'] ?? '') && empty($requestData['pic'])) {
            $this->markTestSkipped(
                'scene=predict_by_title_and_img 时，Step3需要在payload中提供pic（素材中心URL）'
            );
        }

        $result = $this->sdk->callApi(
            'product_GetRecommendCategory\\ProductGetRecommendCategoryRequest',
            'product_GetRecommendCategory\\param\\ProductGetRecommendCategoryParam',
            $requestData,
            $accessToken
        );

        $this->assertIsArray($result, 'Step2.5响应必须为数组');
        $this->assertTrue(
            ProductPublishTestHelper::isApiSuccess($result),
            'Step2.5获取推荐信息失败: code=' . (ProductPublishTestHelper::getApiCode($result) ?? 'NULL')
            . ', msg=' . ProductPublishTestHelper::getApiMessage($result)
            . ', logid=' . ($result['log_id'] ?? 'NULL')
        );

        $logId = (string) ($result['log_id'] ?? '');
        $ids   = ProductPublishTestHelper::extractRecommendIds($result['data'] ?? []);

        return [
            'recommend_ids' => $ids,
            'log_id'        => $logId,
            'request_data'  => $requestData,
        ];
    }

    private function extractCategoryList(array $result): array
    {
        $data = $result['data'] ?? [];

        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }

        if (isset($data['category_list']) && is_array($data['category_list'])) {
            return $data['category_list'];
        }

        if (is_array($data)) {
            $isList = array_keys($data) === range(0, count($data) - 1);

            if ($isList) {
                return $data;
            }
        }

        return [];
    }

}
