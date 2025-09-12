<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Tests\Demo;

use DouDianSdk\Core\Client\DouDianSdk;
use DouDianSdk\Core\Exception\ApiException;
use DouDianSdk\Core\Exception\DouDianException;
use DouDianSdk\Core\Exception\HttpException;
use DouDianSdk\Core\Logger\FileLogger;
use DouDianSdk\Core\Token\AccessToken;
use DouDianSdk\Core\Token\AccessTokenBuilder;
use DouDianSdk\Tests\TestCase;

/**
 * 抖店SDK演示测试.
 *
 * 这个测试类演示了SDK的主要功能和使用方法
 * 可以直接运行来查看SDK的各种功能演示
 */
class DouDianSdkDemoTest extends TestCase
{
    /**
     * 演示SDK基本功能.
     */
    public function testBasicSdkDemo(): void
    {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "🚀 抖店SDK基本功能演示\n";
        echo str_repeat('=', 60) . "\n";

        // 1. 创建SDK实例
        echo "\n1️⃣ 创建SDK实例\n";
        $sdk = new DouDianSdk('demo_app_key', 'demo_app_secret');
        echo "✅ SDK实例创建成功\n";
        echo "   - App Key: demo_app_key\n";
        echo "   - App Secret: demo_app_secret\n";

        // 2. 配置SDK
        echo "\n2️⃣ 配置SDK\n";
        $sdk->setDebug(true);

        // 设置日志记录器
        $logFile = sys_get_temp_dir() . '/doudian-sdk-demo.log';
        $logger  = new FileLogger($logFile, FileLogger::DEBUG);
        $sdk->setLogger($logger);

        echo "✅ SDK配置完成\n";
        echo "   - 调试模式: 开启\n";
        echo "   - 日志文件: {$logFile}\n";

        // 3. 获取配置信息
        echo "\n3️⃣ 获取配置信息\n";
        $config = $sdk->getConfig();
        echo "✅ 配置信息获取成功\n";
        echo "   - 连接超时: {$config->httpConnectTimeout} 毫秒\n";
        echo "   - 读取超时: {$config->httpReadTimeout} 毫秒\n";
        echo '   - 重试机制: ' . ($config->enableRetry ? '开启' : '关闭') . "\n";
        echo "   - 最大重试次数: {$config->maxRetryTimes}\n";

        echo "\n✅ 基本功能演示完成\n";
    }

    /**
     * 演示访问令牌功能.
     */
    public function testAccessTokenDemo(): void
    {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "🔑 访问令牌功能演示\n";
        echo str_repeat('=', 60) . "\n";

        // 1. 演示通过店铺ID获取令牌
        echo "\n1️⃣ 通过店铺ID获取访问令牌\n";

        try {
            $accessToken = AccessTokenBuilder::build('demo_shop_id', \DouDianSdk\Core\Token\ACCESS_TOKEN_SHOP_ID);

            if ($accessToken->isSuccess()) {
                echo "✅ 访问令牌获取成功（模拟）\n";
                echo '   - Token: ' . substr($accessToken->getAccessToken(), 0, 20) . "...\n";
                echo "   - 有效期: {$accessToken->getExpireIn()} 秒\n";
                echo "   - 店铺ID: {$accessToken->getShopId()}\n";
                echo "   - 店铺名称: {$accessToken->getShopName()}\n";
            } else {
                echo "❌ 访问令牌获取失败\n";
                echo "   - 错误码: {$accessToken->getErrNo()}\n";
                echo "   - 错误消息: {$accessToken->getMessage()}\n";
            }
        } catch (DouDianException $e) {
            echo "❌ 获取访问令牌时发生异常: {$e->getMessage()}\n";
        }

        // 2. 演示通过授权码获取令牌
        echo "\n2️⃣ 通过授权码获取访问令牌\n";

        try {
            $accessToken = AccessTokenBuilder::build('demo_auth_code', \DouDianSdk\Core\Token\ACCESS_TOKEN_CODE);

            if ($accessToken->isSuccess()) {
                echo "✅ 通过授权码获取访问令牌成功（模拟）\n";
                echo '   - Token: ' . substr($accessToken->getAccessToken(), 0, 20) . "...\n";
                echo '   - 刷新令牌: ' . substr($accessToken->getRefreshToken(), 0, 20) . "...\n";
                echo "   - 权限范围: {$accessToken->getScope()}\n";
            } else {
                echo "❌ 通过授权码获取访问令牌失败\n";
                echo "   - 错误码: {$accessToken->getErrNo()}\n";
                echo "   - 错误消息: {$accessToken->getMessage()}\n";
            }
        } catch (DouDianException $e) {
            echo "❌ 获取访问令牌时发生异常: {$e->getMessage()}\n";
        }

        // 3. 演示刷新令牌
        echo "\n3️⃣ 刷新访问令牌\n";

        try {
            $refreshedToken = AccessTokenBuilder::refresh('demo_refresh_token');

            if ($refreshedToken->isSuccess()) {
                echo "✅ 刷新访问令牌成功（模拟）\n";
                echo '   - 新Token: ' . substr($refreshedToken->getAccessToken(), 0, 20) . "...\n";
                echo "   - 有效期: {$refreshedToken->getExpireIn()} 秒\n";
            } else {
                echo "❌ 刷新访问令牌失败\n";
                echo "   - 错误码: {$refreshedToken->getErrNo()}\n";
                echo "   - 错误消息: {$refreshedToken->getMessage()}\n";
            }
        } catch (DouDianException $e) {
            echo "❌ 刷新访问令牌时发生异常: {$e->getMessage()}\n";
        }

        echo "\n✅ 访问令牌功能演示完成\n";
    }

    /**
     * 演示API调用功能.
     */
    public function testApiCallDemo(): void
    {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "📡 API调用功能演示\n";
        echo str_repeat('=', 60) . "\n";

        $sdk = new DouDianSdk('demo_app_key', 'demo_app_secret');

        // 创建模拟的访问令牌
        $mockToken = new AccessToken();
        $mockToken->setData((object) [
            'access_token' => 'demo_access_token_123456',
            'expires_in'   => 7200,
            'shop_id'      => 'demo_shop_123',
            'shop_name'    => '演示店铺',
        ]);

        // 1. 演示售后列表API调用
        echo "\n1️⃣ 售后列表API调用演示\n";

        try {
            $result = $sdk->callApi(
                'afterSale_List\AfterSaleListRequest',
                'afterSale_List\param\AfterSaleListParam',
                [
                    'page'       => 1,
                    'size'       => 10,
                    'start_time' => '2024-01-01 00:00:00',
                    'end_time'   => '2024-12-31 23:59:59',
                ],
                $mockToken
            );

            echo "✅ 售后列表API调用成功（模拟）\n";
            echo '   - 响应数据结构: ' . json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        } catch (ApiException $e) {
            echo "❌ API调用失败: {$e->getMessage()}\n";
            echo "   - 错误码: {$e->getApiErrorCode()}\n";
            echo "   - 日志ID: {$e->getLogId()}\n";
        } catch (HttpException $e) {
            echo "❌ HTTP请求失败: {$e->getMessage()}\n";
            echo "   - 状态码: {$e->getHttpStatusCode()}\n";
        } catch (DouDianException $e) {
            echo "❌ SDK错误: {$e->getMessage()}\n";
        }

        // 2. 演示订单列表API调用
        echo "\n2️⃣ 订单列表API调用演示\n";

        try {
            $result = $sdk->callApi(
                'order_searchList\OrderSearchListRequest',
                'order_searchList\param\OrderSearchListParam',
                [
                    'page'         => 1,
                    'size'         => 20,
                    'order_status' => 1,
                    'start_time'   => '2024-01-01 00:00:00',
                    'end_time'     => '2024-12-31 23:59:59',
                ],
                $mockToken
            );

            echo "✅ 订单列表API调用成功（模拟）\n";
            echo '   - 响应数据结构: ' . json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        } catch (DouDianException $e) {
            echo "❌ API调用失败: {$e->getMessage()}\n";
        }

        // 3. 演示商品列表API调用
        echo "\n3️⃣ 商品列表API调用演示\n";

        try {
            $result = $sdk->callApi(
                'product_listV2\ProductListV2Request',
                'product_listV2\param\ProductListV2Param',
                [
                    'page'   => 1,
                    'size'   => 10,
                    'status' => 1,
                ],
                $mockToken
            );

            echo "✅ 商品列表API调用成功（模拟）\n";
            echo '   - 响应数据结构: ' . json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        } catch (DouDianException $e) {
            echo "❌ API调用失败: {$e->getMessage()}\n";
        }

        echo "\n✅ API调用功能演示完成\n";
    }

    /**
     * 演示错误处理功能.
     */
    public function testErrorHandlingDemo(): void
    {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "🛡️ 错误处理功能演示\n";
        echo str_repeat('=', 60) . "\n";

        $sdk       = new DouDianSdk('demo_app_key', 'demo_app_secret');
        $mockToken = new AccessToken();

        // 1. 演示API错误处理
        echo "\n1️⃣ API错误处理演示\n";

        try {
            // 故意使用错误的参数
            $result = $sdk->callApi(
                'order_orderDetail\OrderOrderDetailRequest',
                'order_orderDetail\param\OrderOrderDetailParam',
                [
                    'order_id' => 'invalid_order_id',
                ],
                $mockToken
            );

            echo "ℹ️ API调用完成（模拟）\n";
        } catch (ApiException $e) {
            echo "✅ API错误处理正常\n";
            echo "   - 错误消息: {$e->getMessage()}\n";
            echo "   - 错误码: {$e->getApiErrorCode()}\n";
            echo "   - 日志ID: {$e->getLogId()}\n";
        } catch (HttpException $e) {
            echo "✅ HTTP错误处理正常\n";
            echo "   - 错误消息: {$e->getMessage()}\n";
            echo "   - 状态码: {$e->getHttpStatusCode()}\n";
        } catch (DouDianException $e) {
            echo "✅ SDK错误处理正常\n";
            echo "   - 错误消息: {$e->getMessage()}\n";
        }

        // 2. 演示配置验证
        echo "\n2️⃣ 配置验证演示\n";

        try {
            $config = $sdk->getConfig();
            $config->validate();
            echo "✅ 配置验证通过\n";
        } catch (\InvalidArgumentException $e) {
            echo "❌ 配置验证失败: {$e->getMessage()}\n";
        }

        echo "\n✅ 错误处理功能演示完成\n";
    }

    /**
     * 演示高级功能.
     */
    public function testAdvancedFeaturesDemo(): void
    {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "⚡ 高级功能演示\n";
        echo str_repeat('=', 60) . "\n";

        // 1. 演示自定义HTTP客户端配置
        echo "\n1️⃣ 自定义HTTP客户端配置\n";
        $sdk = new DouDianSdk('demo_app_key', 'demo_app_secret', [
            'debug' => true,
            'retry' => [
                'enable'    => true,
                'max_times' => 5,
                'interval'  => 2000,
            ],
            'timeout' => [
                'connect' => 3000,
                'read'    => 10000,
            ],
        ]);

        echo "✅ 高级配置设置完成\n";
        $config = $sdk->getConfig();
        echo '   - 调试模式: ' . ($config->debug ? '开启' : '关闭') . "\n";
        echo '   - 重试机制: ' . ($config->enableRetry ? '开启' : '关闭') . "\n";
        echo "   - 最大重试次数: {$config->maxRetryTimes}\n";
        echo "   - 重试间隔: {$config->retryInterval} 毫秒\n";
        echo "   - 连接超时: {$config->httpConnectTimeout} 毫秒\n";
        echo "   - 读取超时: {$config->httpReadTimeout} 毫秒\n";

        // 2. 演示日志记录功能
        echo "\n2️⃣ 日志记录功能\n";
        $logFile = sys_get_temp_dir() . '/doudian-sdk-advanced-demo.log';
        $logger  = new FileLogger($logFile, FileLogger::DEBUG);
        $sdk->setLogger($logger);

        echo "✅ 日志记录器设置完成\n";
        echo "   - 日志文件: {$logFile}\n";
        echo "   - 日志级别: DEBUG\n";

        // 记录一些日志
        $logger->info('SDK演示开始');
        $logger->debug('调试信息: 配置已加载');
        $logger->warning('警告信息: 这是演示日志');
        $logger->info('SDK演示结束');

        echo "   - 已记录演示日志\n";

        // 3. 演示性能监控
        echo "\n3️⃣ 性能监控演示\n";
        $startTime = microtime(true);

        // 模拟一些操作
        $this->wait(100); // 等待100毫秒

        $endTime  = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        echo "✅ 性能监控演示完成\n";
        echo '   - 操作耗时: ' . number_format($duration, 2) . " 毫秒\n";

        echo "\n✅ 高级功能演示完成\n";
    }

    /**
     * 演示完整工作流程.
     */
    public function testCompleteWorkflowDemo(): void
    {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "🔄 完整工作流程演示\n";
        echo str_repeat('=', 60) . "\n";

        try {
            // 1. 初始化SDK
            echo "\n1️⃣ 初始化SDK\n";
            $sdk = new DouDianSdk('demo_app_key', 'demo_app_secret');
            $sdk->setDebug(true);
            echo "✅ SDK初始化完成\n";

            // 2. 配置SDK
            echo "\n2️⃣ 配置SDK\n";
            $config = $sdk->getConfig();
            $config->setRetryConfig(true, 3, 1000);
            $config->setTimeout(2000, 8000);
            echo "✅ SDK配置完成\n";

            // 3. 设置日志
            echo "\n3️⃣ 设置日志记录\n";
            $logFile = sys_get_temp_dir() . '/doudian-sdk-workflow-demo.log';
            $logger  = new FileLogger($logFile, FileLogger::INFO);
            $sdk->setLogger($logger);
            echo "✅ 日志记录设置完成\n";

            // 4. 获取访问令牌（模拟）
            echo "\n4️⃣ 获取访问令牌\n";
            $accessToken = new AccessToken();
            $accessToken->setData((object) [
                'access_token'  => 'demo_workflow_token_123456',
                'expires_in'    => 7200,
                'refresh_token' => 'demo_refresh_token_123456',
                'shop_id'       => 'demo_shop_workflow',
                'shop_name'     => '工作流程演示店铺',
            ]);
            echo "✅ 访问令牌获取完成\n";

            // 5. 批量API调用
            echo "\n5️⃣ 批量API调用\n";
            $apis = [
                ['name' => '售后列表', 'api' => 'afterSale_List\\AfterSaleListRequest', 'param' => 'afterSale_List\\param\\AfterSaleListParam'],
                ['name' => '订单列表', 'api' => 'order_searchList\\OrderSearchListRequest', 'param' => 'order_searchList\\param\\OrderSearchListParam'],
                ['name' => '商品列表', 'api' => 'product_listV2\\ProductListV2Request', 'param' => 'product_listV2\\param\\ProductListV2Param'],
            ];

            foreach ($apis as $api) {
                try {
                    echo "   - 调用 {$api['name']} API...\n";
                    $result = $sdk->callApi($api['api'], $api['param'], ['page' => 1, 'size' => 5], $accessToken);
                    echo "     ✅ {$api['name']} API调用成功\n";
                } catch (DouDianException $e) {
                    echo "     ⚠️ {$api['name']} API调用失败: {$e->getMessage()}\n";
                }
            }

            // 6. 错误处理演示
            echo "\n6️⃣ 错误处理演示\n";

            try {
                $sdk->callApi('invalid_api\\InvalidRequest', 'invalid_api\\param\\InvalidParam', [], $accessToken);
            } catch (DouDianException $e) {
                echo "✅ 错误处理正常: {$e->getMessage()}\n";
            }

            echo "\n✅ 完整工作流程演示完成\n";
            echo "📝 日志文件: {$logFile}\n";
        } catch (Exception $e) {
            echo "❌ 工作流程演示失败: {$e->getMessage()}\n";
        }
    }
}
