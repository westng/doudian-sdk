<?php

namespace DouDianSdk\Core;

class AccessTokenBuilder
{
    public static function build($codeOrShopId, $type = ACCESS_TOKEN_CODE): AccessToken
    {
        $request = new \DouDianSdk\Api\Token\CreateTokenRequest();
        $param = new \DouDianSdk\Api\Token\Param\CreateTokenParam();
        if ($type == ACCESS_TOKEN_SHOP_ID) {
            $param->shop_id = $codeOrShopId;
            $param->grant_type = "authorization_self";
            $param->code = "";
        } elseif ($type == ACCESS_TOKEN_CODE) {
            $param->grant_type = "authorization_code";
            $param->code = $codeOrShopId;
        }
        $request->setParam($param);
        $resp = $request->execute(null);

        return AccessToken::wrap($resp);
    }

    public static function refresh($token): AccessToken
    {
        $request = new \DouDianSdk\Api\Token\RefreshTokenRequest();
        $param = new \DouDianSdk\Api\Token\Param\RefreshTokenParam();
        $param->grant_type = "refresh_token";
        if (is_string($token)) {
            $param->refresh_token = $token;
        } else {
            $param->refresh_token = $token->getRefreshToken();
        }
        $request->setParam($param);

        $resp = $request->execute(null);
        return AccessToken::wrap($resp);
    }

    public static function parse($accessTokenStr): AccessToken
    {
        $tokenData = new \DouDianSdk\Api\Token\Data\CreateTokenData();
        $tokenData->access_token = $accessTokenStr;
        $accessToken = new AccessToken();
        $accessToken->setData($tokenData);
        return $accessToken;
    }
}

const ACCESS_TOKEN_CODE = 1;
const ACCESS_TOKEN_SHOP_ID = 2;
