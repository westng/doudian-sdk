<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Tests\Utils;

use DouDianSdk\Tests\TestCase;
use DouDianSdk\Utils\SignUtil;

class SignUtilTest extends TestCase
{
    public function testSign(): void
    {
        $appKey    = 'test_app_key';
        $appSecret = 'test_app_secret';
        $method    = 'test.method';
        $timestamp = 1234567890;
        $paramJson = '{"test": "data"}';

        $sign = SignUtil::sign($appKey, $appSecret, $method, $timestamp, $paramJson);

        $this->assertIsString($sign);
        $this->assertEquals(64, strlen($sign)); // SHA256 hash length
    }

    public function testSignConsistency(): void
    {
        $appKey    = 'test_app_key';
        $appSecret = 'test_app_secret';
        $method    = 'test.method';
        $timestamp = 1234567890;
        $paramJson = '{"test": "data"}';

        $sign1 = SignUtil::sign($appKey, $appSecret, $method, $timestamp, $paramJson);
        $sign2 = SignUtil::sign($appKey, $appSecret, $method, $timestamp, $paramJson);

        $this->assertEquals($sign1, $sign2);
    }

    public function testSignWithDifferentParams(): void
    {
        $appKey    = 'test_app_key';
        $appSecret = 'test_app_secret';
        $method    = 'test.method';
        $timestamp = 1234567890;

        $paramJson1 = '{"test": "data1"}';
        $paramJson2 = '{"test": "data2"}';

        $sign1 = SignUtil::sign($appKey, $appSecret, $method, $timestamp, $paramJson1);
        $sign2 = SignUtil::sign($appKey, $appSecret, $method, $timestamp, $paramJson2);

        $this->assertNotEquals($sign1, $sign2);
    }

    public function testSpiSign(): void
    {
        $appKey     = 'test_app_key';
        $appSecret  = 'test_app_secret';
        $timestamp  = 1234567890;
        $paramJson  = '{"test": "data"}';
        $signMethod = 2; // HMAC-SHA256

        $sign = SignUtil::spiSign($appKey, $appSecret, $timestamp, $paramJson, $signMethod);

        $this->assertIsString($sign);
        $this->assertEquals(64, strlen($sign)); // SHA256 hash length
    }

    public function testSpiSignWithMd5(): void
    {
        $appKey     = 'test_app_key';
        $appSecret  = 'test_app_secret';
        $timestamp  = 1234567890;
        $paramJson  = '{"test": "data"}';
        $signMethod = 1; // MD5

        $sign = SignUtil::spiSign($appKey, $appSecret, $timestamp, $paramJson, $signMethod);

        $this->assertIsString($sign);
        $this->assertEquals(32, strlen($sign)); // MD5 hash length
    }

    public function testMarshal(): void
    {
        $param = (object) [
            'name'   => 'test',
            'value'  => 123,
            'nested' => (object) [
                'key' => 'value',
            ],
        ];

        $result = SignUtil::marshal($param);

        $this->assertIsString($result);
        $decoded = json_decode($result, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('test', $decoded['name']);
        $this->assertEquals(123, $decoded['value']);
        $this->assertEquals('value', $decoded['nested']['key']);
    }

    public function testMarshalWithNull(): void
    {
        $result = SignUtil::marshal(null);

        $this->assertEquals('{}', $result);
    }

    public function testMarshalWithArray(): void
    {
        $param = [
            'name'  => 'test',
            'value' => 123,
        ];

        $result = SignUtil::marshal($param);

        $this->assertIsString($result);
        $decoded = json_decode($result, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('test', $decoded['name']);
        $this->assertEquals(123, $decoded['value']);
    }

    public function testMarshalSorting(): void
    {
        $param = [
            'z' => 'last',
            'a' => 'first',
            'm' => 'middle',
        ];

        $result  = SignUtil::marshal($param);
        $decoded = json_decode($result, true);

        // 验证键已排序
        $keys = array_keys($decoded);
        $this->assertEquals(['a', 'm', 'z'], $keys);
    }

    public function testMarshalNestedSorting(): void
    {
        $param = [
            'nested' => [
                'z' => 'last',
                'a' => 'first',
            ],
            'top' => 'value',
        ];

        $result  = SignUtil::marshal($param);
        $decoded = json_decode($result, true);

        // 验证顶层键已排序
        $topKeys = array_keys($decoded);
        $this->assertEquals(['nested', 'top'], $topKeys);

        // 验证嵌套键已排序
        $nestedKeys = array_keys($decoded['nested']);
        $this->assertEquals(['a', 'z'], $nestedKeys);
    }
}
