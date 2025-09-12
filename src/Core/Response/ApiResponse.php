<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Core\Response;

/**
 * 统一的API响应类.
 */
class ApiResponse
{
    /**
     * @var int 错误码
     */
    public $err_no;

    /**
     * @var string 错误消息
     */
    public $message;

    /**
     * @var mixed 响应数据
     */
    public $data;

    /**
     * @var string 日志ID
     */
    public $log_id;

    /**
     * @var int 响应码（兼容SPI）
     */
    public $code;

    /**
     * 构造函数.
     *
     * @param array $data 响应数据
     */
    public function __construct(array $data = [])
    {
        $this->err_no  = $data['err_no'] ?? 0;
        $this->message = $data['message'] ?? '';
        $this->data    = $data['data'] ?? null;
        $this->log_id  = $data['log_id'] ?? '';
        $this->code    = $data['code'] ?? $this->err_no; // 兼容SPI
    }

    /**
     * 检查是否成功
     */
    public function isSuccess(): bool
    {
        return 0 == $this->err_no || 0 == $this->code;
    }

    /**
     * 获取错误信息.
     */
    public function getErrorMessage(): string
    {
        return $this->message ?: 'Unknown error';
    }

    /**
     * 转换为数组.
     */
    public function toArray(): array
    {
        return [
            'err_no'  => $this->err_no,
            'message' => $this->message,
            'data'    => $this->data,
            'log_id'  => $this->log_id,
            'code'    => $this->code,
        ];
    }

    /**
     * 从数组创建响应对象
     *
     * @param array $data 响应数据
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * 创建成功响应.
     *
     * @param mixed $data 响应数据
     * @param string $message 消息
     */
    public static function success($data = null, string $message = 'success'): self
    {
        return new self([
            'err_no'  => 0,
            'message' => $message,
            'data'    => $data,
            'code'    => 0,
        ]);
    }

    /**
     * 创建错误响应.
     *
     * @param int $errNo 错误码
     * @param string $message 错误消息
     * @param string $logId 日志ID
     */
    public static function error(int $errNo, string $message, string $logId = ''): self
    {
        return new self([
            'err_no'  => $errNo,
            'message' => $message,
            'log_id'  => $logId,
            'code'    => $errNo,
        ]);
    }
}
