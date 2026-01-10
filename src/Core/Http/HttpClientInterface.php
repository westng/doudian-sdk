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

/**
 * HTTP 客户端接口
 * 
 * 统一 Swoole 和 FPM 环境的客户端行为
 */
interface HttpClientInterface
{
    /**
     * 发送 POST 请求
     *
     * @param HttpRequest $request HTTP 请求对象
     * @return HttpResponse HTTP 响应对象
     */
    public function post(HttpRequest $request): HttpResponse;

    /**
     * 发送 GET 请求
     *
     * @param HttpRequest $request HTTP 请求对象
     * @return HttpResponse HTTP 响应对象
     */
    public function get(HttpRequest $request): HttpResponse;

    /**
     * 发送 PUT 请求
     *
     * @param HttpRequest $request HTTP 请求对象
     * @return HttpResponse HTTP 响应对象
     */
    public function put(HttpRequest $request): HttpResponse;

    /**
     * 发送 DELETE 请求
     *
     * @param HttpRequest $request HTTP 请求对象
     * @return HttpResponse HTTP 响应对象
     */
    public function delete(HttpRequest $request): HttpResponse;

    /**
     * 获取默认请求头
     *
     * @return array
     */
    public function getDefaultHeaders(): array;

    /**
     * 设置默认请求头
     *
     * @param array $headers 请求头
     * @return self
     */
    public function setDefaultHeaders(array $headers);
}
