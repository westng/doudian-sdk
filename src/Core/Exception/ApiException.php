<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Core\Exception;

/**
 * API调用异常类.
 */
class ApiException extends DouDianException
{
    /**
     * @var string API错误码
     */
    protected $apiErrorCode;

    /**
     * @var string 日志ID
     */
    protected $logId;

    /**
     * 构造函数.
     *
     * @param string $message 错误消息
     * @param string $apiErrorCode API错误码
     * @param string $logId 日志ID
     * @param int $code 错误码
     * @param \Throwable|null $previous 前一个异常
     */
    public function __construct($message = '', $apiErrorCode = '', $logId = '', $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->apiErrorCode = $apiErrorCode;
        $this->logId        = $logId;
    }

    /**
     * 获取API错误码
     *
     * @return string
     */
    public function getApiErrorCode()
    {
        return $this->apiErrorCode;
    }

    /**
     * 获取日志ID.
     *
     * @return string
     */
    public function getLogId()
    {
        return $this->logId;
    }
}
