<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * 测试基础类.
 *
 * 提供测试中常用的辅助方法和工具
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @var string 测试数据目录
     */
    protected $testDataDir;

    /**
     * @var array 测试配置
     */
    protected $testConfig;

    /**
     * 设置测试环境.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->testDataDir = __DIR__ . '/data';

        // 测试配置
        $this->testConfig = [
            'app_key'    => $_ENV['DOUDIAN_APP_KEY'] ?? 'test_app_key',
            'app_secret' => $_ENV['DOUDIAN_APP_SECRET'] ?? 'test_app_secret',
            'shop_id'    => $_ENV['DOUDIAN_SHOP_ID'] ?? 'test_shop_id',
            'auth_code'  => $_ENV['DOUDIAN_AUTH_CODE'] ?? 'test_auth_code',
        ];
    }

    /**
     * 清理测试环境.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * 获取测试数据目录.
     */
    protected function getTestDataDir(): string
    {
        return $this->testDataDir;
    }

    /**
     * 读取测试数据文件.
     *
     * @param string $filename 文件名
     */
    protected function getTestData(string $filename): string
    {
        $filePath = $this->testDataDir . '/' . $filename;

        if (!file_exists($filePath)) {
            $this->fail("Test data file not found: {$filePath}");
        }

        return file_get_contents($filePath);
    }

    /**
     * 读取JSON测试数据.
     *
     * @param string $filename 文件名
     */
    protected function getJsonTestData(string $filename): array
    {
        $content = $this->getTestData($filename);
        $data    = json_decode($content, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            $this->fail("Invalid JSON in test data file: {$filename}");
        }

        return $data;
    }

    /**
     * 获取测试配置.
     */
    protected function getTestConfig(): array
    {
        return $this->testConfig;
    }

    /**
     * 检查是否在集成测试环境.
     */
    protected function isIntegrationTest(): bool
    {
        return !empty($_ENV['DOUDIAN_INTEGRATION_TEST']) && 'true' === $_ENV['DOUDIAN_INTEGRATION_TEST'];
    }

    /**
     * 跳过集成测试.
     */
    protected function skipIntegrationTest(): void
    {
        if (!$this->isIntegrationTest()) {
            $this->markTestSkipped('Integration test skipped. Set DOUDIAN_INTEGRATION_TEST=true to run.');
        }
    }

    /**
     * 创建临时文件.
     *
     * @param string $content 文件内容
     *
     * @return string 文件路径
     */
    protected function createTempFile(string $content = ''): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'doudian_sdk_test_');
        file_put_contents($tempFile, $content);

        return $tempFile;
    }

    /**
     * 删除临时文件.
     *
     * @param string $filePath 文件路径
     */
    protected function deleteTempFile(string $filePath): void
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * 等待指定时间（用于测试重试机制等）.
     *
     * @param int $milliseconds 毫秒数
     */
    protected function wait(int $milliseconds): void
    {
        usleep($milliseconds * 1000);
    }
}
