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
 * 店铺API测试类.
 */
class ShopApiTest extends TestCase
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
        $this->sdk->setDebug(true);

        if (!empty($this->testConfig['access_token'])) {
            $this->accessToken = $this->testConfig['access_token'];
        } else {
            // 获取访问令牌
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
     * 测试获取店铺体验分.
     */
    public function testGetExperienceScore(): void
    {
        $this->skipIntegrationTest();

        echo "\n=== 测试获取店铺体验分 ===\n";

        try {
            $result = $this->sdk->callApi(
                'shop_getExperienceScore\\ShopGetExperienceScoreRequest',
                [],
                $this->accessToken
            );

            $this->assertIsArray($result, 'API response should be an array');

            echo "✅ 店铺体验分API调用成功\n";
            echo "响应信息:\n";
            echo '  - 错误码: ' . ($result['err_no'] ?? 'NULL') . "\n";
            echo '  - 消息: ' . ($result['message'] ?? 'NULL') . "\n";
            echo '  - 日志ID: ' . ($result['log_id'] ?? 'NULL') . "\n";

            if (isset($result['err_no'])) {
                $this->assertEquals(0, $result['err_no'], 'API should return success');
            }

            if (isset($result['data'])) {
                echo '  - 数据: ' . json_encode($result['data'], JSON_UNESCAPED_UNICODE) . "\n";
            }
        } catch (ApiException $e) {
            echo '⚠️ API错误: ' . $e->getMessage() . "\n";
            echo '  - 错误码: ' . $e->getApiErrorCode() . "\n";
            echo '  - 日志ID: ' . $e->getLogId() . "\n";

            $this->addWarning('API returned business error: ' . $e->getMessage());
        } catch (HttpException $e) {
            echo '❌ HTTP错误: ' . $e->getMessage() . "\n";
            echo '  - 状态码: ' . $e->getHttpStatusCode() . "\n";

            $this->fail('HTTP请求失败: ' . $e->getMessage());
        }
    }
}
