<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Core\Token;

class AccessToken
{
    private $errNo;

    private $message;

    private $subCode;

    private $subMsg;

    private $logId;

    private $data;

    public static function wrap($resp): AccessToken
    {
        $accessToken = new AccessToken();

        // 抖店API响应格式：code/msg/sub_code/sub_msg/data/log_id
        if (isset($resp['code'])) {
            $accessToken->setErrNo($resp['code']);
        }

        if (isset($resp['msg'])) {
            $accessToken->setMessage($resp['msg']);
        }

        if (isset($resp['sub_code'])) {
            $accessToken->setSubCode($resp['sub_code']);
        }

        if (isset($resp['sub_msg'])) {
            $accessToken->setSubMsg($resp['sub_msg']);
        }

        if (isset($resp['log_id'])) {
            $accessToken->setLogId($resp['log_id']);
        }

        if (isset($resp['data'])) {
            $accessToken->setData($resp['data']);
        }

        return $accessToken;
    }

    public function isSuccess(): bool
    {
        // 抖店API：code=10000表示成功
        return 10000 === $this->errNo;
    }

    public function getAccessToken()
    {
        return $this->data['access_token'] ?? null;
    }

    public function getExpireIn()
    {
        return $this->data['expires_in'] ?? null;
    }

    public function getRefreshToken()
    {
        return $this->data['refresh_token'] ?? null;
    }

    public function getScope()
    {
        return $this->data['scope'] ?? null;
    }

    public function getShopId()
    {
        return $this->data['shop_id'] ?? null;
    }

    public function getShopName()
    {
        return $this->data['shop_name'] ?? null;
    }

    public function getErrNo()
    {
        return $this->errNo;
    }

    public function setErrNo($errNo)
    {
        $this->errNo = $errNo;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getSubCode()
    {
        return $this->subCode;
    }

    public function setSubCode($subCode)
    {
        $this->subCode = $subCode;
    }

    public function getSubMsg()
    {
        return $this->subMsg;
    }

    public function setSubMsg($subMsg)
    {
        $this->subMsg = $subMsg;
    }

    public function getLogId()
    {
        return $this->logId;
    }

    public function setLogId($logId)
    {
        $this->logId = $logId;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
