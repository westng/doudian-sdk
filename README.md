# 抖店 SDK

[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.0-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/tests-passing-brightgreen.svg)](#测试)
[![Coverage](https://img.shields.io/badge/coverage-85%25-green.svg)](#测试覆盖率)

抖店（抖音电商）开放平台 PHP SDK，支持 710+ 个 API 接口，经过完整测试验证。

## ✨ 特性

- 🚀 **完整的 API 覆盖**: 支持 710+ 个抖店开放平台接口
- 🔒 **安全的签名机制**: 内置 HMAC-SHA256 签名算法  
- ⚡ **高性能**: 基于 GuzzleHttp 实现，支持并发请求
- 🛡️ **异常处理**: 完善的错误处理和重试机制
- 📦 **易于使用**: 简洁的 API 设计，支持链式调用
- 🧪 **测试覆盖**: 85% 测试覆盖率，核心功能全面验证
- 🔧 **配置灵活**: 支持超时、重试、调试等多种配置
- 🔄 **令牌管理**: 自动令牌刷新和缓存机制

## 安装

```bash
composer require westng/doudian-sdk
```

## 🏗️ SDK架构说明

本SDK采用分层架构设计，遵循单一职责原则：

```
┌─────────────────────────────────────────┐
│              DouDianSdk                 │  ← 门面层：简化API调用
├─────────────────────────────────────────┤
│           DouDianOpClient               │  ← 业务层：处理API请求
├─────────────────────────────────────────┤
│             HttpClient                  │  ← 传输层：HTTP通信
├─────────────────────────────────────────┤
│      GlobalConfig/DouDianOpConfig       │  ← 配置层：统一配置管理
└─────────────────────────────────────────┘
```

### 核心组件

- **DouDianSdk** - 门面类，提供简化的API调用接口
- **DouDianOpClient** - 操作客户端，负责处理API请求和签名
- **HttpClient** - HTTP通信层，基于GuzzleHttp，支持重试和超时
- **GlobalConfig/DouDianOpConfig** - 配置管理，支持单例模式
- **AccessTokenBuilder** - 令牌构建器，支持授权码和店铺ID两种模式
- **Exception** - 完善的异常处理体系（HttpException、ApiException等）

## 🚀 快速开始

### 1. 基础用法（推荐）

```php
<?php
require_once 'vendor/autoload.php';

use DouDianSdk\Core\Client\DouDianSdk;

// 初始化SDK
$sdk = new DouDianSdk('your_app_key', 'your_app_secret');

// 获取访问令牌 (通过店铺ID)
$accessToken = $sdk->getAccessToken('your_shop_id', 2); // 2 = 店铺ID模式

// 检查令牌是否获取成功
if (!$accessToken->isSuccess()) {
    throw new Exception('获取访问令牌失败: ' . $accessToken->getMessage());
}

// 调用API - 获取订单列表
$result = $sdk->callApi(
    'order_searchList\OrderSearchListRequest',
    'order_searchList\param\OrderSearchListParam',
    [
        'page' => 1,
        'size' => 20,
        'order_status' => 1, // 待发货
        'start_time' => date('Y-m-d H:i:s', strtotime('-7 days')),
        'end_time' => date('Y-m-d H:i:s'),
    ],
    $accessToken
);

// 处理结果
if (isset($result['err_no']) && $result['err_no'] == 0) {
    echo "获取订单成功，共 " . count($result['data']['order_list'] ?? []) . " 条订单\n";
    print_r($result['data']);
} else {
    echo "API调用失败: " . ($result['message'] ?? 'Unknown error') . "\n";
}
```

### 2. 高级配置

```php
<?php
require_once 'vendor/autoload.php';

use DouDianSdk\Core\Client\DouDianSdk;

// 创建SDK实例并设置高级配置
$sdk = new DouDianSdk('your_app_key', 'your_app_secret', [
    'debug' => true,
    'timeout' => [
        'connect' => 5000,  // 连接超时5秒（默认）
        'read' => 10000     // 读取超时10秒（默认）
    ],
    'retry' => [
        'enable' => true,   // 启用重试
        'max_times' => 3,   // 最大重试3次
        'interval' => 1000  // 重试间隔1秒
    ]
]);

// 或者分步设置
$sdk->setDebug(true);

// 获取配置信息
$config = $sdk->getConfig();
echo "连接超时: " . $config->httpConnectTimeout . "ms\n";
echo "读取超时: " . $config->httpReadTimeout . "ms\n";
```

### 3. 令牌管理（重要）

```php
<?php
require_once 'vendor/autoload.php';

use DouDianSdk\Core\Client\DouDianSdk;
use DouDianSdk\Core\Token\AccessTokenBuilder;

$sdk = new DouDianSdk('your_app_key', 'your_app_secret');

// 方式1: 通过SDK获取令牌（推荐）
$accessToken = $sdk->getAccessToken('your_shop_id', 2); // 2 = 店铺ID模式

// 方式2: 直接使用AccessTokenBuilder
$accessToken = AccessTokenBuilder::build('your_shop_id', 2);

// 检查令牌是否有效
if ($accessToken->isSuccess()) {
    echo "✅ 令牌获取成功\n";
    echo "访问令牌: " . substr($accessToken->getAccessToken(), 0, 30) . "...\n";
    echo "有效期: " . $accessToken->getExpireIn() . " 秒\n";
    echo "店铺ID: " . $accessToken->getShopId() . "\n";
    echo "店铺名称: " . $accessToken->getShopName() . "\n";
    
    // 如果有刷新令牌，保存它用于后续刷新
    if ($refreshToken = $accessToken->getRefreshToken()) {
        echo "刷新令牌: " . substr($refreshToken, 0, 30) . "...\n";
        
        // 刷新令牌示例
        $newToken = $sdk->refreshAccessToken($refreshToken);
        if ($newToken->isSuccess()) {
            echo "✅ 令牌刷新成功\n";
        }
    }
} else {
    echo "❌ 获取令牌失败: " . $accessToken->getMessage() . "\n";
    echo "错误码: " . $accessToken->getErrNo() . "\n";
}
```

### 4. 错误处理

```php
<?php
require_once 'vendor/autoload.php';

use DouDianSdk\Core\Client\DouDianSdk;
use DouDianSdk\Core\Exception\DouDianException;
use DouDianSdk\Core\Exception\ApiException;
use DouDianSdk\Core\Exception\HttpException;

$sdk = new DouDianSdk('your_app_key', 'your_app_secret');

try {
    // 获取访问令牌
    $accessToken = $sdk->getAccessToken('your_shop_id', 2);
    
    if (!$accessToken->isSuccess()) {
        throw new DouDianException('获取访问令牌失败: ' . $accessToken->getErrMsg());
    }
    
    // 调用API
    $result = $sdk->callApi(
        'order_orderList\\OrderOrderListRequest',
        'order_orderList\\param\\OrderOrderListParam',
        [
            'page' => 1,
            'size' => 20
        ],
        $accessToken->getAccessToken()
    );

    // 检查API返回结果
    if (isset($result['code']) && $result['code'] === 0) {
        // 成功处理
        $data = $result['data'] ?? [];
        echo "获取订单列表成功，共 " . count($data) . " 条记录\n";
    } else {
        // API 返回错误
        $errorMsg = $result['msg'] ?? '未知错误';
        echo "API 错误: " . $errorMsg . "\n";
    }

} catch (HttpException $e) {
    // HTTP 请求错误（网络问题、超时等）
    echo "HTTP 错误: " . $e->getMessage() . "\n";
    
} catch (ApiException $e) {
    // API 调用错误（参数错误、签名错误等）
    echo "API 错误: " . $e->getMessage() . "\n";
    
} catch (DouDianException $e) {
    // 其他 SDK 错误
    echo "SDK 错误: " . $e->getMessage() . "\n";
    
} catch (\Exception $e) {
    // 通用错误处理
    echo "系统错误: " . $e->getMessage() . "\n";
}
```

### 5. 常用 API 示例

#### 订单相关

```php
<?php
// 获取订单列表
$orderList = $sdk->callApi(
    'order_orderList\\OrderOrderListRequest',
    'order_orderList\\param\\OrderOrderListParam',
    [
        'page' => 1,
        'size' => 20,
        'order_by' => 'create_time',
        'is_desc' => 1
    ],
    $accessToken->getAccessToken()
);

// 获取订单详情
$orderDetail = $sdk->callApi(
    'order_orderDetail\\OrderOrderDetailRequest',
    'order_orderDetail\\param\\OrderOrderDetailParam',
    ['order_id' => '123456789'],
    $accessToken->getAccessToken()
);

// 查询订单列表
$result = $sdk->callApi(
    'order_searchList\OrderSearchListRequest',
    'order_searchList\param\OrderSearchListParam',
    [
        'page' => 1,
        'size' => 20,
        'start_time' => '2024-01-01 00:00:00',
        'end_time' => '2024-01-31 23:59:59'
    ],
    $accessToken
);

// 更新订单物流信息
$updateLogistics = $sdk->callApi(
    'order_logisticsEdit\\OrderLogisticsEditRequest',
    'order_logisticsEdit\\param\\OrderLogisticsEditParam',
    [
        'order_id' => '123456789',
        'logistics_id' => 'SF1234567890',
        'company_name' => '顺丰速运'
    ],
    $accessToken->getAccessToken()
);

#### 商品相关

```php
// 获取商品列表
$productList = $sdk->callApi(
    'product_list\\ProductListRequest',
    'product_list\\param\\ProductListParam',
    [
        'page' => 1,
        'size' => 20,
        'status' => 1  // 1=在售
    ],
    $accessToken->getAccessToken()
);

// 获取商品详情
$productDetail = $sdk->callApi(
    'product_detail\\ProductDetailRequest',
    'product_detail\\param\\ProductDetailParam',
    ['product_id' => '123456789'],
    $accessToken->getAccessToken()
);

#### 售后相关

```php
// 获取售后列表
$afterSaleList = $sdk->callApi(
    'afterSale_List\\AfterSaleListRequest',
    'afterSale_List\\param\\AfterSaleListParam',
    [
        'page' => 1,
        'size' => 20,
        'start_time' => '2024-01-01 00:00:00',
        'end_time' => '2024-01-31 23:59:59'
    ],
    $accessToken->getAccessToken()
);

// 处理售后申请
$handleAfterSale = $sdk->callApi(
    'afterSale_refundProcessNotify\\AfterSaleRefundProcessNotifyRequest',
    'afterSale_refundProcessNotify\\param\\AfterSaleRefundProcessNotifyParam',
    [
        'aftersale_id' => '123456789',
        'status' => 1,  // 1=同意，2=拒绝
        'desc' => '处理说明'
    ],
    $accessToken->getAccessToken()
);

### 6. 底层组件直接使用

```php
<?php
require_once 'vendor/autoload.php';

use DouDianSdk\Core\Token\AccessTokenBuilder;
use DouDianSdk\Core\Config\GlobalConfig;
use DouDianSdk\Core\Client\DouDianOpClient;

// 方式1: 使用全局配置
$globalConfig = GlobalConfig::getGlobalConfig();
$globalConfig->setCredentials('your_app_key', 'your_app_secret');

// 获取访问令牌
$accessToken = AccessTokenBuilder::build('your_shop_id', 2);

if ($accessToken->isSuccess()) {
    // 直接使用API类
    $request = new \DouDianSdk\Api\afterSale_List\AfterSaleListRequest();
    $param = new \DouDianSdk\Api\afterSale_List\param\AfterSaleListParam();
    $param->page = 1;
    $param->size = 20;
    $request->setParam($param);
    
    $result = $request->execute($accessToken->getAccessToken());
    print_r($result);
}
```

## 实际项目集成

详细的项目集成指南请参考：[README_INTEGRATION.md](README_INTEGRATION.md)

包含以下框架的完整示例：
- Laravel 项目集成
- Hyperf/MineAdmin 项目集成  
- 通用最佳实践
- 错误处理和重试机制
- 令牌管理和缓存策略

## 📁 项目结构

```
src/
├── Api/                         # API 接口类 (710+ 个接口)
│   ├── order_searchList/        # 订单搜索接口
│   ├── product_listV2/          # 商品列表接口
│   ├── afterSale_List/          # 售后列表接口
│   └── ...                      # 其他710+个接口
├── Core/                        # 核心功能模块
│   ├── Client/                  # 客户端相关类
│   │   ├── DouDianSdk.php       # SDK门面类
│   │   └── DouDianOpClient.php  # API操作客户端
│   ├── Config/                  # 配置管理类
│   │   ├── GlobalConfig.php     # 全局配置（单例）
│   │   └── DouDianOpConfig.php  # 操作配置
│   ├── Token/                   # 访问令牌管理
│   │   ├── AccessToken.php      # 令牌数据类
│   │   └── AccessTokenBuilder.php # 令牌构建器
│   ├── Http/                    # HTTP 客户端
│   │   ├── HttpClient.php       # HTTP客户端
│   │   └── HttpRequest.php      # HTTP请求
│   ├── Exception/               # 异常处理
│   │   ├── DouDianException.php # SDK基础异常
│   │   ├── ApiException.php     # API异常
│   │   └── HttpException.php    # HTTP异常
│   └── Response/                # 响应处理
└── Utils/                       # 工具类
    └── SignUtil.php             # 签名工具

tests/                           # 测试套件
├── Core/                        # 核心功能测试
│   ├── Token/AccessTokenTest.php # 令牌测试
│   └── ConfigTest.php           # 配置测试
├── Api/OrderApiTest.php         # API测试
├── Integration/                 # 集成测试
└── TestCase.php                 # 测试基类
```

## 🧪 测试

### 运行测试

```bash
# 运行所有测试
./vendor/bin/phpunit

# 运行核心功能测试
./vendor/bin/phpunit tests/Core/

# 运行集成测试（需要真实API凭证）
DOUDIAN_INTEGRATION_TEST=true ./vendor/bin/phpunit tests/Core/Token/AccessTokenTest.php

# 生成测试报告
./vendor/bin/phpunit --testdox-html test-report.html
```

### 测试覆盖率

当前测试覆盖率：**85%**

- ✅ **令牌管理**: 100% 覆盖（获取、刷新、验证）
- ✅ **配置管理**: 100% 覆盖（超时、重试、调试）
- ✅ **SDK核心**: 83.3% 覆盖（实例化、API调用）
- ⚠️ **API接口**: 部分覆盖（订单、商品等核心接口）

### 测试环境配置

创建 `.env` 文件：

```bash
DOUDIAN_APP_KEY=your_app_key
DOUDIAN_APP_SECRET=your_app_secret
DOUDIAN_SHOP_ID=your_shop_id
DOUDIAN_REFRESH_TOKEN=your_refresh_token
DOUDIAN_INTEGRATION_TEST=true  # 启用集成测试
```

## 开发

### 安装开发依赖

```bash
composer install --dev
```

### 运行测试

```bash
composer test
```

### 代码风格检查

```bash
composer cs-fixer-check
composer cs-fixer-fix
```

### 静态分析

```bash
composer phpstan
```

## 支持的 API 模块

SDK 支持抖店开放平台的 **710+ 个 API 接口**，涵盖：

- **订单管理**: 订单查询、物流管理、售后服务等
- **商品管理**: 商品发布、库存管理、价格设置等  
- **店铺管理**: 店铺信息、资质管理等
- **营销工具**: 优惠券、满减活动等
- **数据统计**: 销售数据、流量分析等
- **物流服务**: 运费模板、物流公司等
- **财务管理**: 结算单据、账单查询等
- **客服工具**: 消息推送、客服会话等

## 注意事项

1. **访问令牌管理**: 建议使用缓存存储访问令牌，避免频繁请求
2. **错误处理**: 务必处理网络异常和API错误，实现适当的重试机制
3. **频率限制**: 遵守抖店开放平台的API调用频率限制
4. **数据安全**: 妥善保管应用密钥，不要在客户端代码中暴露
5. **版本兼容**: 关注抖店开放平台API版本更新，及时升级SDK

## 许可证

MIT License. 详见 [LICENSE](LICENSE) 文件。

## 贡献

欢迎提交 Issue 和 Pull Request 来完善这个项目。

## 联系方式

- **作者**: westng
- **邮箱**: 457395070@qq.com
- **GitHub**: https://github.com/westng/doudian-sdk-php

## 许可证

本项目采用 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情。

## 贡献

欢迎提交 Issue 和 Pull Request 来改进这个项目。

## 联系方式

- 项目地址: [https://github.com/westng/doudian-sdk](https://github.com/westng/doudian-sdk)
- 问题反馈: [Issues](https://github.com/westng/doudian-sdk/issues)
- 邮箱: 457395070@qq.com
