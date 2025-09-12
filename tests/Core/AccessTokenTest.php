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

use DouDianSdk\Core\Token\AccessToken;
use DouDianSdk\Tests\TestCase;

class AccessTokenTest extends TestCase
{
    public function testWrap(): void
    {
        // 模拟API响应
        $response = (object) [
            'err_no'  => 0,
            'message' => 'success',
            'log_id'  => 'test_log_id',
            'data'    => (object) [
                'access_token'  => 'test_access_token',
                'expires_in'    => 7200,
                'refresh_token' => 'test_refresh_token',
                'scope'         => 'user_info',
                'shop_id'       => '123456',
                'shop_name'     => 'Test Shop',
            ],
        ];

        $accessToken = AccessToken::wrap($response);

        $this->assertInstanceOf(AccessToken::class, $accessToken);
        $this->assertTrue($accessToken->isSuccess());
        $this->assertEquals('test_access_token', $accessToken->getAccessToken());
        $this->assertEquals(7200, $accessToken->getExpireIn());
        $this->assertEquals('test_refresh_token', $accessToken->getRefreshToken());
        $this->assertEquals('user_info', $accessToken->getScope());
        $this->assertEquals('123456', $accessToken->getShopId());
        $this->assertEquals('Test Shop', $accessToken->getShopName());
        $this->assertEquals(0, $accessToken->getErrNo());
        $this->assertEquals('success', $accessToken->getMessage());
        $this->assertEquals('test_log_id', $accessToken->getLogId());
    }

    public function testWrapWithError(): void
    {
        // 模拟API错误响应
        $response = (object) [
            'err_no'  => 1001,
            'message' => 'Invalid request',
            'log_id'  => 'error_log_id',
        ];

        $accessToken = AccessToken::wrap($response);

        $this->assertInstanceOf(AccessToken::class, $accessToken);
        $this->assertFalse($accessToken->isSuccess());
        $this->assertEquals(1001, $accessToken->getErrNo());
        $this->assertEquals('Invalid request', $accessToken->getMessage());
        $this->assertEquals('error_log_id', $accessToken->getLogId());
    }

    public function testWrapWithMinimalResponse(): void
    {
        // 模拟最小响应
        $response = (object) [
            'err_no' => 0,
        ];

        $accessToken = AccessToken::wrap($response);

        $this->assertInstanceOf(AccessToken::class, $accessToken);
        $this->assertTrue($accessToken->isSuccess());
        $this->assertNull($accessToken->getAccessToken());
        $this->assertNull($accessToken->getExpireIn());
        $this->assertNull($accessToken->getRefreshToken());
    }

    public function testGetAccessTokenWithNullData(): void
    {
        $accessToken = new AccessToken();

        $this->assertNull($accessToken->getAccessToken());
    }

    public function testGetExpireInWithNullData(): void
    {
        $accessToken = new AccessToken();

        $this->assertNull($accessToken->getExpireIn());
    }

    public function testGetRefreshTokenWithNullData(): void
    {
        $accessToken = new AccessToken();

        $this->assertNull($accessToken->getRefreshToken());
    }

    public function testGetScopeWithNullData(): void
    {
        $accessToken = new AccessToken();

        $this->assertNull($accessToken->getScope());
    }

    public function testGetShopIdWithNullData(): void
    {
        $accessToken = new AccessToken();

        $this->assertNull($accessToken->getShopId());
    }

    public function testGetShopNameWithNullData(): void
    {
        $accessToken = new AccessToken();

        $this->assertNull($accessToken->getShopName());
    }
}
