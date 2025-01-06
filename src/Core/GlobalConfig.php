<?php

namespace DouDianSdk\Core;

class GlobalConfig extends DouDianOpConfig
{
    private static $instance;

    private function __construct()
    {
    }

    public static function getGlobalConfig(): GlobalConfig
    {

        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone()
    {
    }
}
