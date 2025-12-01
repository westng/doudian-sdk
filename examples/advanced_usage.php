<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

require_once __DIR__ . '/../vendor/autoload.php';

use DouDianSdk\Core\Client\DouDianSdk;
use DouDianSdk\Core\Exception\ApiException;
use DouDianSdk\Core\Exception\DouDianException;
use DouDianSdk\Core\Exception\HttpException;
use DouDianSdk\Core\Http\HttpClient;

// 配置信息（请替换为你的实际信息）
$appKey    = 'your_app_key';
$appSecret = 'your_app_secret';
$shopId    = 'your_shop_id';

try {
    echo "=== 抖店SDK高级使用示例 ===\n\n";

    // 1. 创建自定义HTTP客户端配置
    echo "1. 配置自定义HTTP客户端...\n";
    $httpConfig = [
        'timeout'         => 30,
        'connect_timeout' => 10,
        'verify'          => false, // 在生产环境中应该设为true
        'headers'         => [
            'User-Agent' => 'MyApp/1.0.0 DouDianSDK/1.1.0',
        ],
    ];

    $customHttpClient = new HttpClient($httpConfig);
    HttpClient::setInstance($customHttpClient);
    echo "✓ HTTP客户端配置完成\n\n";

    // 2. 初始化SDK并配置日志
    echo "2. 初始化SDK并配置日志...\n";
    $sdk = new DouDianSdk($appKey, $appSecret, [
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

    // SDK初始化完成
    echo "✓ SDK初始化完成\n\n";

    // 3. 获取访问令牌
    echo "3. 获取访问令牌...\n";
    $accessToken = $sdk->getAccessToken($shopId, ACCESS_TOKEN_SHOP_ID);

    if ($accessToken->isSuccess()) {
        echo "✓ 访问令牌获取成功\n";
        echo '  - Token: ' . substr($accessToken->getAccessToken(), 0, 10) . "...\n";
        echo '  - 有效期: ' . $accessToken->getExpireIn() . " 秒\n";
        echo '  - 店铺ID: ' . $accessToken->getShopId() . "\n";
        echo '  - 店铺名称: ' . $accessToken->getShopName() . "\n";
    } else {
        echo '✗ 访问令牌获取失败: ' . $accessToken->getMessage() . "\n";
        exit(1);
    }
    echo "\n";

    // 4. 批量API调用示例
    echo "4. 批量API调用示例...\n";

    $apiCalls = [
        [
            'name'  => '获取售后列表',
            'api'   => 'afterSale_List\AfterSaleListRequest',
            'param' => 'afterSale_List\param\AfterSaleListParam',
            'data'  => [
                'page'       => 1,
                'size'       => 5,
                'start_time' => date('Y-m-d H:i:s', strtotime('-7 days')),
                'end_time'   => date('Y-m-d H:i:s'),
            ],
        ],
        [
            'name'  => '获取订单列表',
            'api'   => 'order_searchList\OrderSearchListRequest',
            'param' => 'order_searchList\param\OrderSearchListParam',
            'data'  => [
                'page'         => 1,
                'size'         => 5,
                'order_status' => 1,
                'start_time'   => date('Y-m-d H:i:s', strtotime('-3 days')),
                'end_time'     => date('Y-m-d H:i:s'),
            ],
        ],
    ];

    $results = [];

    foreach ($apiCalls as $call) {
        try {
            echo "  - 调用API: {$call['name']}...\n";
            $result                 = $sdk->callApi($call['api'], $call['param'], $call['data'], $accessToken);
            $results[$call['name']] = $result;
            echo "    ✓ 成功\n";
        } catch (ApiException $e) {
            echo '    ✗ API错误: ' . $e->getMessage() . "\n";
            $results[$call['name']] = ['error' => $e->getMessage()];
        } catch (HttpException $e) {
            echo '    ✗ HTTP错误: ' . $e->getMessage() . "\n";
            $results[$call['name']] = ['error' => $e->getMessage()];
        }
    }
    echo "\n";

    // 5. 错误处理和重试机制演示
    echo "5. 错误处理和重试机制演示...\n";

    try {
        // 故意使用错误的参数来触发重试机制
        $sdk->callApi(
            'order_orderDetail\OrderOrderDetailRequest',
            'order_orderDetail\param\OrderOrderDetailParam',
            [
                'order_id' => 'invalid_order_id_that_will_cause_error',
            ],
            $accessToken
        );
    } catch (ApiException $e) {
        echo "✓ API错误处理正常\n";
        echo '  - 错误消息: ' . $e->getMessage() . "\n";
        echo '  - 错误代码: ' . $e->getApiErrorCode() . "\n";
        echo '  - 日志ID: ' . $e->getLogId() . "\n";
    } catch (HttpException $e) {
        echo "✓ HTTP错误处理正常（可能触发了重试）\n";
        echo '  - 错误消息: ' . $e->getMessage() . "\n";
        echo '  - 状态码: ' . $e->getHttpStatusCode() . "\n";
    }
    echo "\n";

    // 6. 性能监控示例
    echo "6. 性能监控示例...\n";
    $startTime = microtime(true);

    try {
        $result = $sdk->callApi(
            'afterSale_List\AfterSaleListRequest',
            'afterSale_List\param\AfterSaleListParam',
            [
                'page' => 1,
                'size' => 1,
            ],
            $accessToken
        );

        $endTime  = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        echo "✓ API调用完成\n";
        echo '  - 耗时: ' . number_format($duration, 2) . " 毫秒\n";
        echo '  - 响应数据大小: ' . strlen(json_encode($result)) . " 字节\n";
    } catch (Exception $e) {
        echo '✗ API调用失败: ' . $e->getMessage() . "\n";
    }
    echo "\n";

    // 7. 配置验证示例
    echo "7. 配置验证示例...\n";
    $config = $sdk->getConfig();

    try {
        $config->validate();
        echo "✓ 配置验证通过\n";
        echo '  - App Key: ' . substr($config->appKey, 0, 10) . "...\n";
        echo '  - 调试模式: ' . ($config->debug ? '开启' : '关闭') . "\n";
        echo '  - 重试机制: ' . ($config->enableRetry ? '开启' : '关闭') . "\n";
        echo '  - 最大重试次数: ' . $config->maxRetryTimes . "\n";
        echo '  - 连接超时: ' . $config->httpConnectTimeout . " 毫秒\n";
        echo '  - 读取超时: ' . $config->httpReadTimeout . " 毫秒\n";
    } catch (InvalidArgumentException $e) {
        echo '✗ 配置验证失败: ' . $e->getMessage() . "\n";
    }
    echo "\n";

    echo "=== 高级示例执行完成 ===\n";
    echo "请查看日志文件以获取详细的调试信息: {$logFile}\n";
} catch (DouDianException $e) {
    echo '✗ SDK错误: ' . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo '✗ 系统错误: ' . $e->getMessage() . "\n";
    exit(1);
}
