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
 * 抖店SDK基础异常类.
 */
class DouDianException extends \Exception
{
    /**
     * @var mixed 错误数据
     */
    protected $errorData;

    /**
     * @var string 错误码
     */
    protected $errorCode;

    /**
     * 构造函数.
     *
     * @param string $message 错误消息
     * @param int $code 错误码
     * @param \Throwable|null $previous 前一个异常
     * @param mixed $errorData 错误数据
     */
    public function __construct($message = '', $code = 0, $previous = null, $errorData = null)
    {
        // PHP 7.0 兼容性：手动检查 previous 类型
        if ($previous !== null && !($previous instanceof \Exception) && !($previous instanceof \Throwable)) {
            throw new \InvalidArgumentException('Previous must be an instance of Exception or Throwable');
        }
        
        parent::__construct($message, $code, $previous);
        $this->errorCode = $code;
        $this->errorData = $errorData;
    }

    /**
     * 获取错误数据.
     */
    public function getErrorData()
    {
        return $this->errorData;
    }

    /**
     * 获取错误码
     *
     * @return string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
}
