<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Core\Config;

use DouDianSdk\Core\SingletonTrait;

/**
 * 全局配置类.
 *
 * 继承自 DouDianOpConfig，提供单例模式的全局配置管理
 */
class GlobalConfig extends DouDianOpConfig
{
    use SingletonTrait;

    /**
     * 获取全局配置实例.
     */
    public static function getGlobalConfig(): GlobalConfig
    {
        return self::getInstance();
    }
}
