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
     * @var array 测试配置
     */
    protected $testConfig;

    /**
     * 设置测试环境.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 加载 .env 文件
        $this->loadEnvFile();

        // 测试配置
        $this->testConfig = [
            'app_key'       => $_ENV['DOUDIAN_APP_KEY'] ?? 'test_app_key',
            'app_secret'    => $_ENV['DOUDIAN_APP_SECRET'] ?? 'test_app_secret',
            'shop_id'       => $_ENV['DOUDIAN_SHOP_ID'] ?? 'test_shop_id',
            'refresh_token' => $_ENV['DOUDIAN_REFRESH_TOKEN'] ?? '',
        ];
    }

    /**
     * 加载环境变量文件.
     */
    protected function loadEnvFile(): void
    {
        $envFile = __DIR__ . '/../.env';

        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // 跳过注释行
            if (empty($line) || 0 === strpos($line, '#')) {
                continue;
            }

            // 解析键值对
            if (false !== strpos($line, '=')) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)]  = trim($value, '"\'');
            }
        }
    }

    /**
     * 跳过集成测试（如果未启用）.
     */
    protected function skipIntegrationTest(): void
    {
        if (!$this->isIntegrationTestEnabled()) {
            $this->markTestSkipped(
                '集成测试未启用。要运行集成测试，请设置环境变量 DOUDIAN_INTEGRATION_TEST=true'
            );
        }
    }

    /**
     * 检查是否启用集成测试.
     */
    protected function isIntegrationTestEnabled(): bool
    {
        return !empty($_ENV['DOUDIAN_INTEGRATION_TEST'])
               && 'true' === $_ENV['DOUDIAN_INTEGRATION_TEST'];
    }

    /**
     * 获取测试配置.
     */
    protected function getTestConfig(): array
    {
        return $this->testConfig;
    }

    /**
     * 验证测试配置是否完整.
     */
    protected function validateTestConfig(): bool
    {
        $required = ['app_key', 'app_secret', 'shop_id'];

        foreach ($required as $key) {
            if (empty($this->testConfig[$key])
                || 0 === strpos($this->testConfig[$key], 'test_')) {
                return false;
            }
        }

        return true;
    }

    /**
     * 断言配置有效.
     */
    protected function assertConfigValid(): void
    {
        $this->assertTrue(
            $this->validateTestConfig(),
            '测试配置无效。请在 .env 文件中设置正确的 DOUDIAN_APP_KEY, DOUDIAN_APP_SECRET, DOUDIAN_SHOP_ID'
        );
    }

    /**
     * 添加警告信息.
     */
    protected function addWarning(string $message): void
    {
        if (method_exists($this, 'addToAssertionCount')) {
            $this->addToAssertionCount(1);
        }

        echo "⚠️ 警告: {$message}\n";
    }
}
