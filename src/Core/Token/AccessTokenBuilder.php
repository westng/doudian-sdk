<?php

declare(strict_types=1);

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Core\Token;

// 访问令牌类型常量
const ACCESS_TOKEN_CODE    = 1;
const ACCESS_TOKEN_SHOP_ID = 2;

use DouDianSdk\Api\token\CreateTokenRequest;
use DouDianSdk\Api\token\data\CreateTokenData;
use DouDianSdk\Api\token\param\CreateTokenParam;
use DouDianSdk\Api\token\param\RefreshTokenParam;
use DouDianSdk\Api\token\RefreshTokenRequest;

class AccessTokenBuilder
{
    public static function build($codeOrShopId, $type = ACCESS_TOKEN_CODE)
    {
        $request = new CreateTokenRequest();
        $param   = new CreateTokenParam();

        if (ACCESS_TOKEN_SHOP_ID == $type) {
            $param->shop_id    = $codeOrShopId;
            $param->grant_type = 'authorization_self';
            $param->code       = '';
        } elseif (ACCESS_TOKEN_CODE == $type) {
            $param->grant_type = 'authorization_code';
            $param->code       = $codeOrShopId;
        }
        $request->setParam($param);
        $resp = $request->execute(null);

        return AccessToken::wrap($resp);
    }

    public static function refresh($token)
    {
        $request           = new RefreshTokenRequest();
        $param             = new RefreshTokenParam();
        $param->grant_type = 'refresh_token';

        if (is_string($token)) {
            $param->refresh_token = $token;
        } else {
            $param->refresh_token = $token->getRefreshToken();
        }
        $request->setParam($param);

        $resp = $request->execute(null);

        return AccessToken::wrap($resp);
    }

    public static function parse($accessTokenStr)
    {
        $tokenData               = new CreateTokenData();
        $tokenData->access_token = $accessTokenStr;
        $accessToken             = new AccessToken();
        $accessToken->setData($tokenData);

        return $accessToken;
    }
}
