# DouDian SDK 测试套件

本测试套件提供了完整的抖店SDK功能测试，包括单元测试、API测试和集成测试。

## 📁 测试结构

```
tests/
├── Core/                   # 核心功能测试
│   └── Token/
│       └── AccessTokenTest.php    # Token获取和刷新测试
├── Api/                    # API调用测试
│   └── OrderApiTest.php           # 订单API测试
├── Integration/            # 集成测试
│   └── SdkIntegrationTest.php     # SDK完整工作流程测试
├── TestCase.php           # 测试基类
└── README.md             # 本文档
```

## 🚀 运行测试

### 环境准备

1. **配置环境变量**
   
   复制 `.env.example` 为 `.env` 并填入真实配置：
   ```bash
   DOUDIAN_APP_KEY=your_app_key
   DOUDIAN_APP_SECRET=your_app_secret
   DOUDIAN_SHOP_ID=your_shop_id
   DOUDIAN_REFRESH_TOKEN=your_refresh_token  # 可选，用于刷新token测试
   DOUDIAN_INTEGRATION_TEST=true             # 启用集成测试
   ```

2. **安装依赖**
   ```bash
   composer install
   ```

### 运行测试命令

```bash
# 运行所有测试
./vendor/bin/phpunit

# 运行特定测试套件
./vendor/bin/phpunit --testsuite="Unit Tests"      # 单元测试
./vendor/bin/phpunit --testsuite="API Tests"       # API测试
./vendor/bin/phpunit --testsuite="Integration Tests" # 集成测试

# 运行特定测试文件
./vendor/bin/phpunit tests/Core/Token/AccessTokenTest.php
./vendor/bin/phpunit tests/Api/OrderApiTest.php
./vendor/bin/phpunit tests/Integration/SdkIntegrationTest.php

# 生成覆盖率报告
./vendor/bin/phpunit --coverage-html coverage
```

## 📋 测试内容

### 1. Token测试 (`Core/Token/AccessTokenTest.php`)

- ✅ **获取访问令牌** - 通过店铺ID获取访问令牌
- ✅ **刷新访问令牌** - 使用刷新令牌获取新的访问令牌
- ✅ **AccessTokenBuilder直接调用** - 测试底层Token构建器

**运行命令：**
```bash
./vendor/bin/phpunit tests/Core/Token/AccessTokenTest.php
```

### 2. API测试 (`Api/OrderApiTest.php`)

- ✅ **获取订单列表** - 测试订单搜索API
- ✅ **获取订单详情** - 测试订单详情API
- ✅ **API参数验证** - 测试参数验证机制

**运行命令：**
```bash
./vendor/bin/phpunit tests/Api/OrderApiTest.php
```

### 3. 集成测试 (`Integration/SdkIntegrationTest.php`)

- ✅ **完整工作流程** - 获取Token → 刷新Token → 调用API
- ✅ **SDK配置测试** - 测试超时、重试等配置
- ✅ **错误处理测试** - 测试各种错误场景

**运行命令：**
```bash
./vendor/bin/phpunit tests/Integration/SdkIntegrationTest.php
```

## ⚙️ 测试配置

### 集成测试开关

集成测试需要真实的API凭证，默认关闭。要启用集成测试：

1. **通过环境变量：**
   ```bash
   export DOUDIAN_INTEGRATION_TEST=true
   ./vendor/bin/phpunit
   ```

2. **通过 .env 文件：**
   ```
   DOUDIAN_INTEGRATION_TEST=true
   ```

3. **临时启用：**
   ```bash
   DOUDIAN_INTEGRATION_TEST=true ./vendor/bin/phpunit
   ```

### 测试配置说明

- `DOUDIAN_APP_KEY` - 应用Key（必需）
- `DOUDIAN_APP_SECRET` - 应用密钥（必需）
- `DOUDIAN_SHOP_ID` - 店铺ID（必需）
- `DOUDIAN_REFRESH_TOKEN` - 刷新令牌（可选，用于刷新token测试）
- `DOUDIAN_INTEGRATION_TEST` - 是否启用集成测试（默认false）

## 📊 测试报告

### 覆盖率报告

```bash
# 生成HTML覆盖率报告
./vendor/bin/phpunit --coverage-html coverage

# 生成Clover XML报告
./vendor/bin/phpunit --coverage-clover coverage.xml
```

### 测试输出

测试运行时会显示详细的调试信息：

```
=== 测试获取访问令牌（通过店铺ID） ===
使用配置:
  - App Key: 7579520684666144291
  - Shop ID: 91637799

调试信息:
  - 错误码: 0
  - 消息: success
  - 日志ID: 20251206165003231E8F2F827EB9C86328
✅ 访问令牌获取成功
  - Token: dx1ojkfg7n1ll2zud75fx2b0001ik4...
  - 有效期: 494694 秒
```

## 🔧 自定义测试

### 扩展测试基类

```php
<?php
namespace DouDianSdk\Tests\YourModule;

use DouDianSdk\Tests\TestCase;

class YourTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // 你的初始化代码
    }
    
    public function testYourFeature(): void
    {
        $this->skipIntegrationTest(); // 如果需要跳过集成测试
        
        // 你的测试代码
        $this->assertTrue(true);
    }
}
```

### 添加新的测试套件

在 `phpunit.xml` 中添加：

```xml
<testsuite name="Your Tests">
    <directory>tests/YourModule</directory>
</testsuite>
```

## 🐛 故障排除

### 常见问题

1. **测试跳过：** 检查是否设置了 `DOUDIAN_INTEGRATION_TEST=true`
2. **认证失败：** 检查 `.env` 文件中的凭证是否正确
3. **网络超时：** 检查网络连接，可能需要VPN
4. **API错误：** 检查店铺权限和API参数

### 调试模式

所有测试都启用了调试模式，会显示详细的请求和响应信息。

## 📝 贡献测试

欢迎贡献新的测试用例！请遵循以下规范：

1. 继承 `TestCase` 基类
2. 使用描述性的测试方法名
3. 添加适当的断言
4. 包含必要的注释和文档
5. 考虑集成测试的开关控制
