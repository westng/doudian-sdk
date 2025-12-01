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

// 配置信息（请替换为你的实际信息）
$appKey    = 'your_app_key';
$appSecret = 'your_app_secret';
$shopId    = 'your_shop_id';

try {
    echo "=== 抖店SDK基础使用示例 ===\n\n";

    // 1. 初始化SDK
    echo "1. 初始化SDK...\n";
    $sdk = new DouDianSdk($appKey, $appSecret);

    // 启用调试模式
    $sdk->setDebug(true);

    echo "✓ SDK初始化完成\n\n";

    // 2. 获取访问令牌
    echo "2. 获取访问令牌...\n";
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

    // 3. 调用API - 获取售后列表
    echo "3. 调用API - 获取售后列表...\n";
    $result = $sdk->callApi(
        'afterSale_List\AfterSaleListRequest',
        'afterSale_List\param\AfterSaleListParam',
        [
            'page'       => 1,
            'size'       => 10,
            'start_time' => date('Y-m-d H:i:s', strtotime('-7 days')),
            'end_time'   => date('Y-m-d H:i:s'),
        ],
        $accessToken
    );

    echo "✓ API调用成功\n";
    echo '  - 响应数据: ' . json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

    // 4. 调用API - 获取订单列表
    echo "4. 调用API - 获取订单列表...\n";
    $orderResult = $sdk->callApi(
        'order_searchList\OrderSearchListRequest',
        'order_searchList\param\OrderSearchListParam',
        [
            'page'         => 1,
            'size'         => 5,
            'order_status' => 1,
            'start_time'   => date('Y-m-d H:i:s', strtotime('-3 days')),
            'end_time'     => date('Y-m-d H:i:s'),
        ],
        $accessToken
    );

    echo "✓ 订单列表API调用成功\n";
    echo '  - 订单数量: ' . count($orderResult['data']['list'] ?? []) . "\n\n";

    // 5. 错误处理示例
    echo "5. 错误处理示例...\n";

    try {
        // 故意使用错误的参数来触发错误
        $sdk->callApi(
            'order_orderDetail\OrderOrderDetailRequest',
            'order_orderDetail\param\OrderOrderDetailParam',
            [
                'order_id' => 'invalid_order_id',
            ],
            $accessToken
        );
    } catch (ApiException $e) {
        echo "✓ API错误处理正常\n";
        echo '  - 错误消息: ' . $e->getMessage() . "\n";
        echo '  - 错误代码: ' . $e->getApiErrorCode() . "\n";
        echo '  - 日志ID: ' . $e->getLogId() . "\n";
    } catch (HttpException $e) {
        echo "✓ HTTP错误处理正常\n";
        echo '  - 错误消息: ' . $e->getMessage() . "\n";
        echo '  - 状态码: ' . $e->getHttpStatusCode() . "\n";
    } catch (DouDianException $e) {
        echo "✓ SDK错误处理正常\n";
        echo '  - 错误消息: ' . $e->getMessage() . "\n";
    }
    echo "\n";

    echo "=== 示例执行完成 ===\n";
} catch (DouDianException $e) {
    echo '✗ SDK错误: ' . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo '✗ 系统错误: ' . $e->getMessage() . "\n";
    exit(1);
}
