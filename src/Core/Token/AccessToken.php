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
        if (property_exists($resp, 'code')) {
            $accessToken->setErrNo($resp->code);
        }

        if (property_exists($resp, 'msg')) {
            $accessToken->setMessage($resp->msg);
        }

        if (property_exists($resp, 'sub_code')) {
            $accessToken->setSubCode($resp->sub_code);
        }

        if (property_exists($resp, 'sub_msg')) {
            $accessToken->setSubMsg($resp->sub_msg);
        }

        if (property_exists($resp, 'log_id')) {
            $accessToken->setLogId($resp->log_id);
        }

        if (property_exists($resp, 'data')) {
            $accessToken->setData($resp->data);
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
        if (null != $this->data && property_exists($this->data, 'access_token')) {
            return $this->data->access_token;
        }

        return null;
    }

    public function getExpireIn()
    {
        if (null != $this->data && property_exists($this->data, 'expires_in')) {
            return $this->data->expires_in;
        }

        return null;
    }

    public function getRefreshToken()
    {
        if (null != $this->data && property_exists($this->data, 'refresh_token')) {
            return $this->data->refresh_token;
        }

        return null;
    }

    public function getScope()
    {
        if (null != $this->data && property_exists($this->data, 'scope')) {
            return $this->data->scope;
        }

        return null;
    }

    public function getShopId()
    {
        if (null != $this->data && property_exists($this->data, 'shop_id')) {
            return $this->data->shop_id;
        }

        return null;
    }

    public function getShopName()
    {
        if (null != $this->data && property_exists($this->data, 'shop_name')) {
            return $this->data->shop_name;
        }

        return null;
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
