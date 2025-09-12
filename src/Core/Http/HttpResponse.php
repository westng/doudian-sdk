<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Core\Http;

class HttpResponse
{
    /**
     * @var int HTTP状态码
     */
    public $statusCode;

    /**
     * @var string 响应体
     */
    public $body;

    /**
     * @var array 响应头
     */
    public $headers = [];

    /**
     * @var array 请求信息
     */
    public $requestInfo = [];

    /**
     * @var float 请求开始时间
     */
    public $startTime;

    /**
     * @var float 请求结束时间
     */
    public $endTime;

    /**
     * 构造函数.
     */
    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    /**
     * 设置响应头.
     *
     * @param array $headers 响应头数组
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * 获取响应头.
     *
     * @param string $name 头名称（可选）
     *
     * @return array|string|null
     */
    public function getHeader($name = null)
    {
        if (null === $name) {
            return $this->headers;
        }

        return $this->headers[$name] ?? null;
    }

    /**
     * 获取JSON解码后的响应体.
     *
     * @param bool $assoc 是否返回关联数组
     */
    public function getJson($assoc = true)
    {
        return json_decode($this->body, $assoc);
    }

    /**
     * 检查请求是否成功
     */
    public function isSuccess(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * 获取请求耗时（毫秒）.
     */
    public function getDuration(): float
    {
        if (null === $this->endTime) {
            $this->endTime = microtime(true);
        }

        return ($this->endTime - $this->startTime) * 1000;
    }

    /**
     * 获取请求信息.
     *
     * @param string $key 信息键名（可选）
     */
    public function getRequestInfo($key = null)
    {
        if (null === $key) {
            return $this->requestInfo;
        }

        return $this->requestInfo[$key] ?? null;
    }
}
