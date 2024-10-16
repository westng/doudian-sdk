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

class AccessToken
{
    private $errNo;

    private $message;

    private $logId;

    private $data;

    public static function wrap($resp)
    {
        $accessToken = new AccessToken();
        if (property_exists($resp, 'err_no')) {
            $accessToken->setErrNo($resp->err_no);
        }
        if (property_exists($resp, 'message')) {
            $accessToken->setMessage($resp->message);
        }
        if (property_exists($resp, 'log_id')) {
            $accessToken->setLogId($resp->log_id);
        }
        if (property_exists($resp, 'data')) {
            $accessToken->setData($resp->data);
        }
        return $accessToken;
    }

    public function isSuccess()
    {
        return $this->errNo == 0;
    }

    public function getAccessToken()
    {
        if ($this->data != null && property_exists($this->data, 'access_token')) {
            return $this->data->access_token;
        }
        return null;
    }

    public function getExpireIn()
    {
        if ($this->data != null && property_exists($this->data, 'expires_in')) {
            return $this->data->expires_in;
        }
        return null;
    }

    public function getRefreshToken()
    {
        if ($this->data != null && property_exists($this->data, 'refresh_token')) {
            return $this->data->refresh_token;
        }
        return null;
    }

    public function getScope()
    {
        if ($this->data != null && property_exists($this->data, 'scope')) {
            return $this->data->scope;
        }
        return null;
    }

    public function getShopId()
    {
        if ($this->data != null && property_exists($this->data, 'shop_id')) {
            return $this->data->shop_id;
        }
        return null;
    }

    public function getShopName()
    {
        if ($this->data != null && property_exists($this->data, 'shop_name')) {
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

    // 新增将AccessToken转换为数组的方法
    public function toArray()
    {
        return [
            'errNo' => $this->getErrNo() ?? false,
            'message' => $this->getMessage() ?? '请求成功',
            'logId' => $this->getLogId(),
            'data' => $this->data ? (array) $this->data : null,
        ];
    }
}
