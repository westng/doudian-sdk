<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Core\Swoole;

/**
 * 连接池配置类
 */
class PoolConfig
{
    /**
     * @var int 最大连接数
     */
    public $maxConnections = 50;

    /**
     * @var int 最大空闲时间（秒）
     */
    public $maxIdleTime = 60;

    /**
     * @var float 等待超时时间（秒）
     */
    public $waitTimeout = 3.0;

    /**
     * 从数组创建配置
     *
     * @param array $config 配置数组
     * @return self
     */
    public static function fromArray(array $config): self
    {
        $instance = new self();

        if (isset($config['max_connections'])) {
            $instance->maxConnections = (int) $config['max_connections'];
        }

        if (isset($config['max_idle_time'])) {
            $instance->maxIdleTime = (int) $config['max_idle_time'];
        }

        if (isset($config['wait_timeout'])) {
            $instance->waitTimeout = (float) $config['wait_timeout'];
        }

        return $instance;
    }

    /**
     * 转换为数组
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'max_connections' => $this->maxConnections,
            'max_idle_time' => $this->maxIdleTime,
            'wait_timeout' => $this->waitTimeout,
        ];
    }

    /**
     * 验证配置
     *
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        if ($this->maxConnections <= 0) {
            throw new \InvalidArgumentException(
                'max_connections must be greater than 0, got: ' . $this->maxConnections
            );
        }

        if ($this->maxConnections > 1000) {
            throw new \InvalidArgumentException(
                'max_connections must not exceed 1000, got: ' . $this->maxConnections
            );
        }

        if ($this->maxIdleTime < 0) {
            throw new \InvalidArgumentException(
                'max_idle_time must be non-negative, got: ' . $this->maxIdleTime
            );
        }

        if ($this->waitTimeout <= 0) {
            throw new \InvalidArgumentException(
                'wait_timeout must be greater than 0, got: ' . $this->waitTimeout
            );
        }
    }
}
