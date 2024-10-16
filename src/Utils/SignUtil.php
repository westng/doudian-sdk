<?php
/*
 * @Date: 2022-08-08 13:39:59
 * @LastEditors: west_ng 457395070@qq.com
 * @LastEditTime: 2024-10-16 15:51:03
 * @FilePath: /NQJL-Backend/vendor/zkl/doudian-sdk-php/src/Utils/SignUtil.php
 */

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace DoudianSdkPhp\Utils;

class SignUtil
{
    public static function sign($appKey, $appSecret, $method, $timestamp, $paramJson)
    {
        $paramPattern = 'app_key' . $appKey . 'method' . $method . 'param_json' . $paramJson . 'timestamp' . $timestamp . 'v2';
        $signPattern = $appSecret . $paramPattern . $appSecret;
        echo 'sign_pattern:' . $signPattern . "\n";
        return hash_hmac('sha256', $signPattern, $appSecret);
    }

    public static function spiSign($appKey, $appSecret, $timestamp, $paramJson, $signMethod)
    {
        $paramPattern = 'app_key' . $appKey . 'param_json' . $paramJson . 'timestamp' . $timestamp;
        $signPattern = $appSecret . $paramPattern . $appSecret;
        // print('spi sign_pattern: ' . $signPattern. "\n");
        if ($signMethod == 2) {
            return hash_hmac('sha256', $signPattern, $appSecret);
        }
        return md5($signPattern);
    }

    // 序列化参数，入参必须为关联数组
    public static function marshal($param)
    {
        if ($param == null) {
            return '{}';
        }
        $arr = self::objToArray($param);
        SignUtil::recKSort($arr); // 对关联数组中的kv，执行排序，需要递归
        return json_encode($arr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); // 重新序列化，确保所有key按字典序排序
    }

    // 关联数组排序，递归
    private static function recKSort(&$arr)
    {
        $kstring = true;
        foreach ($arr as $k => &$v) {
            if (! is_string($k)) {
                $kstring = false;
            }
            if (is_array($v)) {
                SignUtil::recKSort($v);
            }
        }
        if ($kstring) {
            ksort($arr);
        }
    }

    private static function objToArray($obj)
    {
        // 先转成json string
        $jsonStr = json_encode($obj);
        // 再转成array
        return json_decode($jsonStr, true);
    }
}
