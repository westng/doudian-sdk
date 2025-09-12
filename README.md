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

## 快速开始

```php
<?php
use DouDianSdk\Core\Client\DouDianSdk;

// 初始化SDK
$sdk = new DouDianSdk('your_app_key', 'your_app_secret');

// 获取访问令牌
$accessToken = $sdk->getAccessToken('your_shop_id', ACCESS_TOKEN_SHOP_ID);

// 调用API
$result = $sdk->callApi(
    'afterSale_List\AfterSaleListRequest',
    'afterSale_List\param\AfterSaleListParam',
    [
        'page' => 1,
        'size' => 20,
        'start_time' => '2024-01-01 00:00:00',
        'end_time' => '2024-01-31 23:59:59'
    ],
    $accessToken
);

print_r($result);
```

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
│   ├── Logger/                  # 日志记录
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

- **订单管理**: 订单查询、物流管理、售后服务等
- **商品管理**: 商品发布、库存管理、价格设置等
- **店铺管理**: 店铺信息、资质管理等
- **营销工具**: 优惠券、满减活动等
- **数据统计**: 销售数据、流量分析等
- **更多模块**: 查看 `src/Api/` 目录了解完整列表

## 更新日志

### v1.3.0 (2024-12-19)

- 🗂️ **目录结构优化**: 重新组织 Core 目录结构，按功能模块分类
- 🔧 **命名空间重构**: 更新所有相关类的命名空间，提高代码组织性
- 📚 **文档更新**: 更新 README 文档以反映新的目录结构
- 🧹 **清理冗余文件**: 删除不必要的开发脚本文件

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
