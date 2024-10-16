<?php
/*
 * @Date: 2022-08-08 13:39:59
 * @LastEditors: west_ng 457395070@qq.com
 * @LastEditTime: 2024-10-16 12:23:35
 * @FilePath: /NQJL-Backend/vendor/zkl/doudian-sdk-php/src/Core/DoudianOpClient.php
 */

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

use DoudianSdkPhp\Core\Http\HttpClient;
use DoudianSdkPhp\Core\Http\HttpRequest;
use DoudianSdkPhp\Utils\SignUtil;

class DoudianOpClient
{
    private $httpClient;

    private static $defaultInstance;

    public function __construct()
    {
        $this->httpClient = HttpClient::getInstance();
    }

    public function request($request, $accessToken)
    {
        $config = $request->getConfig();
        $urlPath = $request->getUrlPath();
        $method = $this->getMethod($urlPath);
        $paramJson = SignUtil::marshal($request->getParam());
        $appKey = $config->appKey;
        $appSecret = $config->appSecret;
        $timestamp = time();
        $sign = SignUtil::sign($appKey, $appSecret, $method, $timestamp, $paramJson);
        //        print $sign . "\n";
        $openHost = $config->openRequestUrl;
        $accessTokenStr = '';
        if ($accessToken != null) {
            // $accessTokenStr = $accessToken->getAccessToken();
            $accessTokenStr = is_object($accessToken) ? $accessToken->getAccessToken() : $accessToken;
        }

        // String requestUrlPattern = "%s/%s?app_key=%s&method=%s&v=2&sign=%s&timestamp=%s&access_token=%s";
        $requestUrl = $openHost . $urlPath . '?app_key=' . $appKey . '&method=' . $method . '&v=2&sign=' . $sign . '&timestamp=' . $timestamp . '&access_token=' . $accessTokenStr . '&sign_method=hmac-sha256';

        // 发送http请求
        $httpRequest = new HttpRequest();
        $httpRequest->url = $requestUrl;
        $httpRequest->body = $paramJson;
        $httpRequest->connectTimeout = $config->httpConnectTimeout;
        $httpRequest->readTimeout = $config->httpReadTimeout;
        $httpResponse = $this->httpClient->post($httpRequest);

        return json_decode($httpResponse->body, false, 512, JSON_UNESCAPED_UNICODE);
    }

    public static function getInstance()
    {
        if (! self::$defaultInstance instanceof self) {
            self::$defaultInstance = new self();
        }
        return self::$defaultInstance;
    }

    private function getMethod($urlPath)
    {
        if (strlen($urlPath) == 0) {
            return $urlPath;
        }
        $methodPath = '';
        if (substr($urlPath, 0, 1) == '/') {
            $methodPath = substr($urlPath, 1, strlen($urlPath));
        } else {
            $methodPath = $urlPath;
        }
        return str_replace('/', '.', $methodPath);
    }
}
