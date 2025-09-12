<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

spl_autoload_register(function ($class) {
    // 检查是否是抖店SDK的类
    if (0 !== strpos($class, 'DouDianSdk\\')) {
        return;
    }

    // 将命名空间转换为文件路径
    $classPath = str_replace('DouDianSdk\\', '', $class);
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $classPath);

    // 构建完整的文件路径
    $filePath = __DIR__ . DIRECTORY_SEPARATOR . $classPath . '.php';

    // 如果文件存在，则引入
    if (file_exists($filePath)) {
        require_once $filePath;
    }
});

// 定义常量
if (!defined('ACCESS_TOKEN_CODE')) {
    define('ACCESS_TOKEN_CODE', 1);
}

if (!defined('ACCESS_TOKEN_SHOP_ID')) {
    define('ACCESS_TOKEN_SHOP_ID', 2);
}
