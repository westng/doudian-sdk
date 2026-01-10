# 抖店 SDK

[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.2-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/tests-passing-brightgreen.svg)](#测试)
[![Coverage](https://img.shields.io/badge/coverage-85%25-green.svg)](#测试覆盖率)
[![Swoole](https://img.shields.io/badge/swoole-supported-orange.svg)](#swoole-协程支持)

抖店（抖音电商）开放平台 PHP SDK，支持 710+ 个 API 接口，**原生支持 Swoole 协程环境和连接池**。

## ✨ 特性

- 🚀 **完整的 API 覆盖**: 支持 710+ 个抖店开放平台接口
- 🔒 **安全的签名机制**: 内置 HMAC-SHA256 签名算法  
- ⚡ **高性能**: 基于 GuzzleHttp 实现，支持并发请求
- 🌊 **Swoole 协程支持**: 原生支持 Swoole 协程环境，Worker 级别连接池
- 🔗 **连接池**: HTTP 连接复用，避免 "Too many open files" 问题
- 🛡️ **异常处理**: 完善的错误处理和重试机制
- 📦 **易于使用**: 简洁的 API 设计，支持链式调用
- 🧪 **测试覆盖**: 85% 测试覆盖率，核心功能全面验证
- 🔧 **配置灵活**: 支持超时、重试、调试、连接池等多种配置
- 🔄 **令牌管理**: 自动令牌刷新和缓存机制

## 安装

```bash
composer require westng/doudian-sdk
```

### Swoole 环境（可选）

如果在 Swoole 协程环境下使用，推荐安装协程 HTTP 客户端：

```bash
# 推荐：安装 hyperf/guzzle 获得最佳连接池支持
composer require hyperf/guzzle
```

## 🏗️ SDK架构说明

本SDK采用分层架构设计，遵循单一职责原则：

```
┌─────────────────────────────────────────┐
│              DouDianSdk                 │  ← 门面层：简化API调用
├─────────────────────────────────────────┤
│           DouDianOpClient               │  ← 业务层：处理API请求
├─────────────────────────────────────────┤
│         HttpClientFactory               │  ← 工厂层：环境自适应
├──────────────────┬──────────────────────┤
│   HttpClient     │   SwooleHttpClient   │  ← 传输层：HTTP通信
│   (FPM环境)      │   + ConnectionPool   │
│                  │   (Swoole协程环境)    │
├──────────────────┴──────────────────────┤
│      GlobalConfig/DouDianOpConfig       │  ← 配置层：统一配置管理
└─────────────────────────────────────────┘
```

### 核心组件

- **DouDianSdk** - 门面类，提供简化的API调用接口
- **DouDianOpClient** - 操作客户端，负责处理API请求和签名
- **HttpClientFactory** - HTTP客户端工厂，自动检测环境选择合适的客户端
- **ConnectionPool** - Worker 级别共享的连接池，避免连接泄漏
- **HttpClient** - 标准HTTP客户端，适用于 PHP-FPM 环境
- **SwooleHttpClient** - 协程安全HTTP客户端，使用共享连接池
- **GlobalConfig/DouDianOpConfig** - 配置管理
- **AccessTokenBuilder** - 令牌构建器，支持授权码和店铺ID两种模式

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
        'order_status' => 1,
        'start_time' => date('Y-m-d H:i:s', strtotime('-7 days')),
        'end_time' => date('Y-m-d H:i:s'),
    ],
    $accessToken
);

// 处理结果
if (isset($result['code']) && $result['code'] === 10000) {
    echo "获取订单成功\n";
    print_r($result['data']);
}
```

### 2. 高级配置

```php
<?php
use DouDianSdk\Core\Client\DouDianSdk;

$sdk = new DouDianSdk('your_app_key', 'your_app_secret', [
    'debug' => true,
    'timeout' => [
        'connect' => 5000,  // 连接超时5秒
        'read' => 10000     // 读取超时10秒
    ],
    'retry' => [
        'enable' => true,
        'max_times' => 3,
        'interval' => 1000
    ],
    // Swoole 环境连接池配置
    'pool' => [
        'max_connections' => 50,  // 最大连接数
        'max_idle_time' => 60,    // 空闲超时（秒）
        'wait_timeout' => 3.0     // 等待超时（秒）
    ]
]);
```

## 🌊 Swoole 协程支持

SDK 从 v2.1.0 开始原生支持 Swoole 协程环境，**智能适配**不同的运行环境。

### 连接池策略

SDK 会自动检测环境，选择最优方案：

| 优先级 | 环境 | 策略 | 说明 |
|-------|------|------|------|
| 1 | Hyperf + PoolHandler | 直接使用 Hyperf 连接池 | 最优方案，充分利用 Hyperf |
| 2 | Hyperf + CoroutineHandler | SDK 连接池 + Hyperf 协程 | 兼容方案 |
| 3 | 原生 Swoole | SDK 连接池 | 兜底方案 |
| 4 | PHP-FPM | 进程级单例 | 传统模式 |

### 解决的问题

| 问题 | 之前 | 现在 |
|------|------|------|
| Too many open files | 每个协程创建独立连接，连接数无限增长 | 连接池限制最大连接数 |
| 连接泄漏 | 协程结束时连接未正确释放 | 请求完成立即归还，finally 保证 |
| TCP 握手开销 | 每次请求都要握手 | 连接复用，减少握手 |
| 资源不可控 | 无法限制连接数 | 可配置最大连接数 |

### 连接池架构

```
┌─────────────────────────────────────────┐
│              Worker Process              │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐   │
│  │Coroutine│ │Coroutine│ │Coroutine│   │
│  │   #1    │ │   #2    │ │   #3    │   │
│  └────┬────┘ └────┬────┘ └────┬────┘   │
│       │           │           │         │
│       └───────────┼───────────┘         │
│                   ▼                     │
│    ┌───────────────────────────────┐    │
│    │      ConnectionPool           │    │
│    │  ┌─────┐ ┌─────┐ ┌─────┐     │    │
│    │  │Conn1│ │Conn2│ │Conn3│ ... │    │  ← Worker 级别共享
│    │  └─────┘ └─────┘ └─────┘     │    │
│    │      (max: 50 connections)    │    │
│    └───────────────────────────────┘    │
└─────────────────────────────────────────┘
```

### 连接池工作流程

```php
// 每次 HTTP 请求
$client = $pool->get();      // 1. 从池获取连接（或等待）
try {
    $response = $client->request(...);  // 2. 发送请求
    return $response;
} finally {
    $pool->put($client);     // 3. 归还连接（无论成功失败）
}
```

### Swoole 环境配置

```php
<?php
use DouDianSdk\Core\Client\DouDianSdk;

// 方式1：构造函数配置
$sdk = new DouDianSdk('your_app_key', 'your_app_secret', [
    'pool' => [
        'max_connections' => 50,   // 最大连接数（默认50）
        'max_idle_time' => 60,     // 空闲超时秒数（默认60）
        'wait_timeout' => 3.0      // 等待超时秒数（默认3.0）
    ]
]);

// 方式2：运行时配置
$sdk->setPoolConfig(100, 120, 5.0);
```

### 队列消费者示例

```php
<?php
use DouDianSdk\Core\Client\DouDianSdk;

// 初始化 SDK（在 Worker 启动时）
$sdk = new DouDianSdk('your_app_key', 'your_app_secret', [
    'pool' => ['max_connections' => 50]
]);

$count = 0;

while (true) {
    $job = $queue->pop();
    
    // 每个协程共享同一个连接池
    go(function() use ($sdk, $job) {
        $accessToken = getAccessTokenFromCache($job['shop_id']);
        
        $result = $sdk->callApi(
            'order_orderDetail\OrderOrderDetailRequest',
            'order_orderDetail\param\OrderOrderDetailParam',
            ['order_id' => $job['order_id']],
            $accessToken
        );
        
        // 处理结果...
        // 请求完成，连接自动归还到池中
    });
    
    $count++;
    
    // 定期查看连接池状态
    if ($count % 1000 === 0) {
        $stats = $sdk->getPoolStats();
        echo sprintf(
            "连接池: 活跃=%d, 空闲=%d, 等待=%d, 总请求=%d\n",
            $stats['active_connections'],
            $stats['idle_connections'],
            $stats['wait_queue_size'],
            $stats['total_requests']
        );
    }
}
```

### 连接池监控

```php
$stats = $sdk->getPoolStats();

// 返回结构：
// [
//     'pool_size' => 50,           // 池大小配置
//     'total_created' => 50,       // 总创建连接数
//     'active_connections' => 30,  // 正在使用的连接
//     'idle_connections' => 20,    // 空闲可用的连接
//     'wait_queue_size' => 5,      // 等待获取连接的协程数
//     'total_requests' => 10000,   // 总请求数
// ]

// 调优建议：
// - wait_queue_size 经常 > 0 → 增加 max_connections
// - idle_connections 经常很高 → 减少 max_connections
```

### 资源释放

```php
// 在长时间运行的进程中，可以主动释放资源
$sdk->shutdown();

// 或者在 Worker 退出时调用
Swoole\Process::signal(SIGTERM, function() use ($sdk) {
    $sdk->shutdown();
    exit(0);
});
```

### Hyperf 框架集成（推荐）

Hyperf 用户可以获得最佳性能，SDK 会自动使用 Hyperf 的 PoolHandler：

```php
<?php
// 1. 安装 hyperf/guzzle（如果还没安装）
// composer require hyperf/guzzle

// 2. config/autoload/dependencies.php
return [
    \DouDianSdk\Core\Client\DouDianSdk::class => function() {
        return new \DouDianSdk\Core\Client\DouDianSdk(
            env('DOUDIAN_APP_KEY'),
            env('DOUDIAN_APP_SECRET'),
            [
                'pool' => [
                    'max_connections' => 50,  // Hyperf PoolHandler 会使用这个配置
                    'max_idle_time' => 60,
                ]
            ]
        );
    },
];

// 3. 在 Controller 或 Service 中使用
class OrderService
{
    public function __construct(
        private \DouDianSdk\Core\Client\DouDianSdk $sdk
    ) {}
    
    public function getOrder(string $orderId, string $accessToken)
    {
        return $this->sdk->callApi(...);
        // SDK 自动使用 Hyperf PoolHandler，无需额外配置
    }
}
```

检查是否使用了 Hyperf PoolHandler：

```php
$stats = $sdk->getPoolStats();
echo $stats['mode'];  
// 'hyperf-pool-handler' - 使用 Hyperf 内置连接池（最优）
// 'sdk-pool-with-hyperf-handler' - SDK 连接池 + Hyperf 协程
// 'sdk-pool-native' - SDK 连接池 + 原生 Swoole
```

### 环境检测

```php
// 检查当前环境
echo $sdk->getEnvironment();  
// 'swoole-coroutine' - Swoole 协程环境
// 'swoole-sync' - Swoole 同步环境
// 'fpm' - PHP-FPM 环境

echo $sdk->isSwooleCoroutine() ? '协程环境' : '非协程环境';
```

## 🔑 令牌管理

```php
<?php
use DouDianSdk\Core\Client\DouDianSdk;

$sdk = new DouDianSdk('your_app_key', 'your_app_secret');

// 获取令牌
$accessToken = $sdk->getAccessToken('your_shop_id', 2);

if ($accessToken->isSuccess()) {
    echo "访问令牌: " . $accessToken->getAccessToken() . "\n";
    echo "有效期: " . $accessToken->getExpireIn() . " 秒\n";
    echo "店铺ID: " . $accessToken->getShopId() . "\n";
    
    // 刷新令牌
    if ($refreshToken = $accessToken->getRefreshToken()) {
        $newToken = $sdk->refreshAccessToken($refreshToken);
    }
} else {
    echo "错误: " . $accessToken->getMessage() . "\n";
    echo "子错误码: " . $accessToken->getSubCode() . "\n";
}
```

## 🛡️ 错误处理

```php
<?php
use DouDianSdk\Core\Client\DouDianSdk;
use DouDianSdk\Core\Exception\DouDianException;
use DouDianSdk\Core\Exception\ApiException;
use DouDianSdk\Core\Exception\HttpException;

try {
    $result = $sdk->callApi(...);
} catch (HttpException $e) {
    // HTTP 错误（网络问题、超时、连接池耗尽等）
    echo "HTTP 错误: " . $e->getMessage() . "\n";
} catch (ApiException $e) {
    // API 错误（参数错误、签名错误等）
    echo "API 错误: " . $e->getMessage() . "\n";
} catch (DouDianException $e) {
    // 其他 SDK 错误
    echo "SDK 错误: " . $e->getMessage() . "\n";
}
```

## 📁 项目结构

```
src/
├── Api/                         # API 接口类 (710+ 个接口)
├── Core/
│   ├── Client/                  # 客户端
│   │   ├── DouDianSdk.php       # SDK门面类
│   │   └── DouDianOpClient.php  # API操作客户端
│   ├── Config/                  # 配置管理
│   ├── Token/                   # 访问令牌管理
│   ├── Http/                    # HTTP 客户端
│   │   ├── HttpClient.php       # 标准HTTP客户端
│   │   ├── SwooleHttpClient.php # Swoole协程客户端
│   │   ├── HttpClientFactory.php# 客户端工厂
│   │   └── HttpClientInterface.php
│   ├── Swoole/                  # Swoole 支持
│   │   ├── ConnectionPool.php   # 连接池（核心）
│   │   ├── RuntimeDetector.php  # 环境检测
│   │   ├── PoolConfig.php       # 连接池配置
│   │   └── CoroutineContext.php # 协程上下文
│   ├── Exception/               # 异常处理
│   └── Response/                # 响应处理
└── Utils/                       # 工具类
```

## 🧪 测试

```bash
# 运行所有测试
./vendor/bin/phpunit

# 运行连接池测试
./vendor/bin/phpunit tests/Core/ConnectionPoolTest.php

# 运行 Swoole 兼容性测试
./vendor/bin/phpunit tests/Core/SwooleCompatibilityTest.php
```

## 📝 更新日志

### v2.1.0 (2026-01-11)

**重要更新：Swoole 协程支持 & 智能连接池**

#### 新增功能
- 🌊 **Swoole 协程支持**：原生支持 Swoole 协程环境
- 🔗 **智能连接池策略**：
  - 优先使用 Hyperf PoolHandler（最优方案）
  - 其次使用 SDK 连接池 + Hyperf CoroutineHandler
  - 兜底使用 SDK 连接池 + 原生 Swoole
- 🚀 **Hyperf 深度集成**：自动检测并使用 Hyperf 内置连接池
- 📊 **连接池监控**：`getPoolStats()` 获取连接池状态和模式
- 🔧 **资源管理**：`shutdown()` 方法显式释放资源

#### 新增类
- `ConnectionPool` - Worker 级别共享的连接池（兜底方案）
- `HttpClientFactory` - HTTP 客户端工厂
- `SwooleHttpClient` - Swoole 协程安全客户端（智能选择策略）
- `RuntimeDetector` - 运行时环境检测
- `PoolConfig` - 连接池配置

#### 兼容性
- ✅ 完全向后兼容，现有代码无需修改
- ✅ PHP >= 7.2
- ✅ 支持 Swoole 4.5+
- ✅ 支持 Hyperf 2.x / 3.x

### v2.0.0 (2024-12-11)

- 完善错误处理和响应解析
- 新增 `sub_code` 和 `sub_msg` 字段支持

## 注意事项

1. **访问令牌管理**: 建议使用缓存存储访问令牌
2. **IP白名单**: 确保服务器IP已添加到抖店开放平台白名单
3. **频率限制**: 遵守抖店开放平台的API调用频率限制
4. **Swoole 环境**: 推荐安装 `hyperf/guzzle` 获得最佳连接池支持
5. **连接池调优**: 根据 `getPoolStats()` 返回的数据调整连接池大小

## 许可证

MIT License - 查看 [LICENSE](LICENSE) 文件了解详情。

## 联系方式

- **作者**: westng
- **邮箱**: 457395070@qq.com
- **项目地址**: [https://github.com/westng/doudian-sdk-php](https://github.com/westng/doudian-sdk-php)
