# 抖店 SDK

[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.0-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

抖店（抖音电商）开放平台 PHP SDK，支持 710+ 个 API 接口。

## 特性

- 🚀 **完整的 API 覆盖**: 支持 710+个抖店开放平台接口
- 🔒 **安全的签名机制**: 内置 HMAC-SHA256 签名算法
- ⚡ **高性能**: 基于 GuzzleHttp 实现
- 🛡️ **异常处理**: 完善的错误处理机制
- 📦 **易于使用**: 简洁的 API 设计

## 安装

```bash
composer require westng/doudian-sdk
```

## SDK架构说明

本SDK采用分层架构设计，主要组件：

- **DouDianSdk** - 门面类，提供简化的API调用接口
- **DouDianOpClient** - 操作客户端，负责处理API请求
- **HttpClient** - HTTP通信层，基于GuzzleHttp
- **GlobalConfig/DouDianOpConfig** - 配置管理，支持单例模式
- **AccessTokenBuilder** - 令牌构建器，支持授权码和店铺ID两种模式
- **Exception** - 完善的异常处理体系

## 快速开始

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
    throw new Exception('获取访问令牌失败: ' . $accessToken->getErrMsg());
}

// 调用API
$result = $sdk->callApi(
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

print_r($result);
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
        'connect' => 2000,  // 连接超时2秒
        'read' => 10000     // 读取超时10秒
    ],
    'retry' => [
        'enable' => true,
        'max_times' => 3,
        'interval' => 1000  // 重试间隔1秒
    ]
]);

// 或者分步设置
$sdk->setDebug(true);

// 获取访问令牌
$accessToken = $sdk->getAccessToken('your_shop_id', 2);

if ($accessToken->isSuccess()) {
    // 调用API
    $result = $sdk->callApi(
        'order_orderDetail\\OrderOrderDetailRequest',
        'order_orderDetail\\param\\OrderOrderDetailParam',
        ['order_id' => '123456789'],
        $accessToken->getAccessToken()
    );
    
    print_r($result);
} else {
    echo '令牌获取失败: ' . $accessToken->getErrMsg();
}
```

### 3. 访问令牌管理

```php
<?php
require_once 'vendor/autoload.php';

use DouDianSdk\Core\Client\DouDianSdk;
use DouDianSdk\Core\Token\AccessTokenBuilder;

// 访问令牌类型常量
// 1 = 通过授权码获取
// 2 = 通过店铺ID获取

$sdk = new DouDianSdk('your_app_key', 'your_app_secret');

// 方式1: 通过SDK获取令牌（推荐）
$accessToken = $sdk->getAccessToken('your_shop_id', 2);

// 方式2: 直接使用AccessTokenBuilder
$accessToken = AccessTokenBuilder::build('your_shop_id', 2);

// 检查令牌是否有效
if ($accessToken->isSuccess()) {
    echo "访问令牌: " . $accessToken->getAccessToken() . "\n";
    echo "有效期: " . $accessToken->getExpireIn() . " 秒\n";
    echo "刷新令牌: " . $accessToken->getRefreshToken() . "\n";
} else {
    echo "获取令牌失败: " . $accessToken->getErrMsg() . "\n";
}

// 刷新令牌
if ($accessToken->isSuccess() && $accessToken->getRefreshToken()) {
    $newToken = $sdk->refreshAccessToken($accessToken->getRefreshToken());
    
    if ($newToken->isSuccess()) {
        echo "令牌刷新成功\n";
    }
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

## 项目结构

```
src/
├── Api/                         # API 接口类 (710+ 个接口)
├── Core/                        # 核心功能模块
│   ├── Client/                  # 客户端相关类
│   ├── Config/                  # 配置管理类
│   ├── Token/                   # 访问令牌管理
│   ├── Response/                # 响应处理
│   ├── Exception/               # 异常处理
│   ├── Http/                    # HTTP 客户端
│   └── Validator/               # 参数验证
└── Utils/                       # 工具类
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

## 更新日志

### v1.3.0 (2024-12-01)
- 📚 **文档重构**: 完全重写README文档，修正所有错误示例
- 🏗️ **架构说明**: 添加详细的SDK分层架构说明
- 🔧 **修复引用**: 修正命名空间引用和常量使用错误
- 📖 **集成指南**: 新增详细的项目集成指南和最佳实践
- ⚠️ **错误处理**: 完善错误处理示例和重试机制
- 🎯 **实用示例**: 提供更多实际项目中的使用示例
- 🧹 **移除Logger**: 从SDK核心移除Logger功能，简化架构，Logger移至tests目录

### v1.0.0 (2024-01-01)
- 🚀 **首次发布**: 支持 710+ 个抖店开放平台 API 接口
- 🏛️ **分层架构**: 采用分层架构设计，职责分离
- 🛡️ **异常处理**: 完善的错误处理机制
- 🔑 **令牌管理**: 支持访问令牌自动管理和刷新
- 📦 **易于集成**: 提供多种框架集成示例

### v1.2.0 (2024-10-14)

- 🎨 **新增 PHP CS Fixer 支持**: 提供更强大的代码风格检查和自动修复功能
- 🧹 **代码结构优化**: 删除冗余的 SPI 相关类，统一响应处理
- 🔧 **统一单例模式**: 使用 SingletonTrait 统一所有单例类的实现

### v1.1.0 (2024-10-14)

- ✨ 新增完整的异常处理体系
- ✨ 新增自动重试机制
- ✨ 新增完善的日志记录功能
- 🔄 **重构 HTTP 客户端**: 使用 GuzzleHttp 替代 cURL
- ✨ 新增便捷的 SDK 入口类
- 🧪 **新增完整的测试套件**

### v1.0.0 (初始版本)

- 🎉 初始版本发布
- 📦 支持 710+ 个抖店开放平台 API
- 🔒 完整的签名和认证机制

## 许可证

本项目采用 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情。

## 贡献

欢迎提交 Issue 和 Pull Request 来改进这个项目。

## 联系方式

- 项目地址: [https://github.com/westng/doudian-sdk](https://github.com/westng/doudian-sdk)
- 问题反馈: [Issues](https://github.com/westng/doudian-sdk/issues)
- 邮箱: 457395070@qq.com
