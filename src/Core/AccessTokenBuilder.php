<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace DoudianSdkPhp\Core;

use DoudianSdkPhp\Api\Token\CreateTokenRequest;
use DoudianSdkPhp\Api\Token\Data\CreateTokenData;
use DoudianSdkPhp\Api\Token\Param\CreateTokenParam;
use DoudianSdkPhp\Api\Token\Param\RefreshTokenParam;
use DoudianSdkPhp\Api\Token\RefreshTokenRequest;

class AccessTokenBuilder
{
    public static function build($codeOrShopId, $type = ACCESS_TOKEN_CODE)
    {
        $request = new CreateTokenRequest();
        $param = new CreateTokenParam();
        if ($type == ACCESS_TOKEN_SHOP_ID) {
            $param->shop_id = $codeOrShopId;
            $param->grant_type = 'authorization_self';
            $param->code = '';
        } elseif ($type == ACCESS_TOKEN_CODE) {
            $param->grant_type = 'authorization_code';
            $param->code = $codeOrShopId;
        }
        $request->setParam($param);
        $resp = $request->execute(null);

        return AccessToken::wrap($resp);
    }

    public static function refresh($token)
    {
        $request = new RefreshTokenRequest();
        $param = new RefreshTokenParam();
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
        $tokenData = new CreateTokenData();
        $tokenData->access_token = $accessTokenStr;
        $accessToken = new AccessToken();
        $accessToken->setData($tokenData);
        return $accessToken;
    }
}

const ACCESS_TOKEN_CODE = 1;
const ACCESS_TOKEN_SHOP_ID = 2;
