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
 * HTTP请求异常类.
 */
class HttpException extends DouDianException
{
    /**
     * @var int HTTP状态码
     */
    protected $httpStatusCode;

    /**
     * @var string 响应体
     */
    protected $responseBody;

    /**
     * 构造函数.
     *
     * @param string $message 错误消息
     * @param int $httpStatusCode HTTP状态码
     * @param string $responseBody 响应体
     * @param int $code 错误码
     * @param \Throwable|null $previous 前一个异常
     */
    public function __construct($message = '', $httpStatusCode = 0, $responseBody = '', $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->httpStatusCode = $httpStatusCode;
        $this->responseBody   = $responseBody;
    }

    /**
     * 获取HTTP状态码
     *
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * 获取响应体.
     *
     * @return string
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }
}
