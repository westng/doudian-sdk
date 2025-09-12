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

class HttpRequest
{
    /**
     * @var string 请求URL
     */
    public $url;

    /**
     * @var string 请求体
     */
    public $body;

    /**
     * @var array 请求头
     */
    public $headers = [];

    /**
     * @var int 连接超时时间（毫秒）
     */
    public $connectTimeout = 1000;

    /**
     * @var int 读取超时时间（毫秒）
     */
    public $readTimeout = 5000;

    /**
     * @var string 请求方法
     */
    public $method = 'POST';

    /**
     * @var array 查询参数
     */
    public $queryParams = [];

    /**
     * 构造函数.
     *
     * @param string $url 请求URL
     * @param string $body 请求体
     * @param array $headers 请求头
     */
    public function __construct($url = '', $body = '', array $headers = [])
    {
        $this->url     = $url;
        $this->body    = $body;
        $this->headers = $headers;
    }

    /**
     * 设置请求头.
     *
     * @param string $name 头名称
     * @param string $value 头值
     */
    public function setHeader($name, $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * 设置多个请求头.
     *
     * @param array $headers 请求头数组
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * 添加查询参数.
     *
     * @param string $name 参数名
     * @param string $value 参数值
     */
    public function addQueryParam($name, $value): self
    {
        $this->queryParams[$name] = $value;

        return $this;
    }

    /**
     * 构建完整URL（包含查询参数）.
     */
    public function buildUrl(): string
    {
        if (empty($this->queryParams)) {
            return $this->url;
        }

        $separator = false !== strpos($this->url, '?') ? '&' : '?';

        return $this->url . $separator . http_build_query($this->queryParams);
    }
}
