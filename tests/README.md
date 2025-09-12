# 测试文档

本目录包含了抖店 SDK 的完整测试套件，涵盖了单元测试、集成测试和演示测试。

## 📁 测试结构

```
tests/
├── README.md                    # 本文档
├── TestCase.php                 # 测试基础类
├── Core/                        # 核心功能测试
│   ├── DouDianSdkTest.php      # SDK 主类测试
│   └── AccessTokenTest.php     # 访问令牌测试
├── Http/                        # HTTP 客户端测试
│   └── HttpClientTest.php      # HTTP 客户端功能测试
├── Integration/                 # 集成测试
│   └── DouDianSdkIntegrationTest.php # 真实 API 集成测试
├── Demo/                        # 演示测试
│   └── DouDianSdkDemoTest.php  # 功能演示和示例
├── Utils/                       # 工具类测试
│   └── SignUtilTest.php        # 签名工具测试
└── data/                        # 测试数据
    ├── sample_api_response.json # 示例 API 响应
    ├── error_api_response.json  # 错误 API 响应
    └── after_sale_list_response.json # 售后列表响应
```

## 🚀 运行测试

### 1. 安装依赖

```bash
# 安装开发依赖
composer install --dev
```

### 2. 运行所有测试

```bash
# 运行完整测试套件
composer test

# 或者使用 Makefile
make test
```

### 3. 运行特定测试套件

```bash
# 运行单元测试
composer test-unit

# 运行集成测试
composer test-integration

# 运行演示测试
composer test-demo

# 运行所有测试
composer test-all
```

### 4. 运行单个测试文件

```bash
# 运行 SDK 核心测试
./vendor/bin/phpunit tests/Core/DouDianSdkTest.php

# 运行 HTTP 客户端测试
./vendor/bin/phpunit tests/Http/HttpClientTest.php

# 运行演示测试
./vendor/bin/phpunit tests/Demo/DouDianSdkDemoTest.php
```

### 5. 生成测试覆盖率报告

```bash
# 生成覆盖率报告
composer test-coverage

# 报告将保存在 coverage/ 目录中
```

## 📋 测试类型说明

### 1. 单元测试 (Unit Tests)

**位置**: `tests/Core/`, `tests/Http/`, `tests/Utils/`

**目的**: 测试单个类或方法的功能

**特点**:
- 快速执行
- 不依赖外部服务
- 使用 Mock 对象
- 测试边界条件

**示例**:
```php
public function testSdkInitialization(): void
{
    $sdk = new DouDianSdk('test_key', 'test_secret');
    $this->assertEquals('test_key', $sdk->getAppKey());
    $this->assertEquals('test_secret', $sdk->getAppSecret());
}
```

### 2. 集成测试 (Integration Tests)

**位置**: `tests/Integration/`

**目的**: 测试与真实 API 的集成

**特点**:
- 需要真实的环境变量
- 可能调用真实 API
- 测试完整的业务流程
- 需要网络连接

**环境变量配置**:
```bash
export DOUDIAN_APP_KEY="your_real_app_key"
export DOUDIAN_APP_SECRET="your_real_app_secret"
export DOUDIAN_SHOP_ID="your_real_shop_id"
```

### 3. 演示测试 (Demo Tests)

**位置**: `tests/Demo/`

**目的**: 展示 SDK 的各种使用场景

**特点**:
- 不执行真实断言
- 输出演示信息
- 展示最佳实践
- 提供使用示例

**演示内容**:
- 基本 SDK 使用
- 访问令牌获取
- API 调用示例
- 错误处理演示
- 高级功能展示

## 🛠️ 测试配置

### PHPUnit 配置

配置文件: `phpunit.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Core</directory>
            <directory>tests/Http</directory>
            <directory>tests/Utils</directory>
        </testsuite>
        
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
        
        <testsuite name="Demo">
            <directory>tests/Demo</directory>
        </testsuite>
    </testsuites>
    
    <php>
        <env name="APP_ENV" value="testing"/>
    </php>
</phpunit>
```

### 环境变量

集成测试需要以下环境变量：

```bash
# 抖店应用凭证
DOUDIAN_APP_KEY=your_app_key
DOUDIAN_APP_SECRET=your_app_secret

# 店铺信息
DOUDIAN_SHOP_ID=your_shop_id

# 测试模式
DOUDIAN_TEST_MODE=true
```

## 📊 测试数据

### 测试数据文件

**位置**: `tests/data/`

**文件说明**:
- `sample_api_response.json`: 正常 API 响应示例
- `error_api_response.json`: 错误 API 响应示例
- `after_sale_list_response.json`: 售后列表 API 响应

**使用示例**:
```php
public function testApiResponse(): void
{
    $responseData = $this->getTestData('sample_api_response.json');
    $this->assertArrayHasKey('code', $responseData);
    $this->assertEquals(0, $responseData['code']);
}
```

## 🔧 编写测试

### 测试基础类

所有测试类都应该继承 `TestCase`：

```php
<?php

use DouDianSdk\Tests\TestCase;

class MyTest extends TestCase
{
    public function testSomething(): void
    {
        // 测试代码
    }
}
```

### 常用断言

```php
// 基本断言
$this->assertTrue($condition);
$this->assertFalse($condition);
$this->assertEquals($expected, $actual);
$this->assertNotEquals($expected, $actual);

// 数组断言
$this->assertArrayHasKey('key', $array);
$this->assertContains($needle, $haystack);

// 异常断言
$this->expectException(\Exception::class);
$this->expectExceptionMessage('Error message');

// 对象断言
$this->assertInstanceOf(\SomeClass::class, $object);
```

### Mock 对象

```php
// 创建 Mock 对象
$mockHttpClient = $this->createMock(HttpClient::class);

// 配置 Mock 行为
$mockHttpClient->method('get')
    ->willReturn($mockResponse);

// 验证 Mock 调用
$mockHttpClient->expects($this->once())
    ->method('post')
    ->with($expectedUrl, $expectedData);
```

## 🐛 调试测试

### 1. 详细输出

```bash
# 显示详细输出
./vendor/bin/phpunit --verbose

# 显示测试过程
./vendor/bin/phpunit --debug
```

### 2. 过滤测试

```bash
# 运行特定测试方法
./vendor/bin/phpunit --filter testMethodName

# 运行包含特定字符串的测试
./vendor/bin/phpunit --filter "testSdk"
```

### 3. 停止失败

```bash
# 第一个测试失败时停止
./vendor/bin/phpunit --stop-on-failure

# 第一个错误时停止
./vendor/bin/phpunit --stop-on-error
```

## 📈 测试最佳实践

### 1. 测试命名

```php
// 好的命名
public function testShouldReturnAccessTokenWhenValidCredentials(): void

// 避免的命名
public function test1(): void
```

### 2. 测试结构 (AAA 模式)

```php
public function testMethodName(): void
{
    // Arrange - 准备测试数据
    $sdk = new DouDianSdk('key', 'secret');
    $expectedToken = 'mock_token';
    
    // Act - 执行被测试的方法
    $result = $sdk->getAccessToken('shop_id');
    
    // Assert - 验证结果
    $this->assertEquals($expectedToken, $result->getAccessToken());
}
```

### 3. 测试独立性

- 每个测试应该独立运行
- 不依赖其他测试的状态
- 使用 setUp() 和 tearDown() 清理状态

### 4. 测试覆盖率

- 目标：80% 以上的代码覆盖率
- 重点测试核心业务逻辑
- 测试边界条件和异常情况

## 🚨 常见问题

### 1. 测试失败

**问题**: 测试失败但代码看起来正确

**解决方案**:
- 检查环境变量设置
- 验证 Mock 对象配置
- 查看详细的错误信息

### 2. 集成测试超时

**问题**: 集成测试执行时间过长

**解决方案**:
- 检查网络连接
- 使用测试专用的 API 环境
- 设置合理的超时时间

### 3. Mock 不工作

**问题**: Mock 对象没有按预期工作

**解决方案**:
- 检查 Mock 对象的方法签名
- 确保调用了正确的方法
- 验证 Mock 对象的配置

## 📚 更多资源

- [PHPUnit 官方文档](https://phpunit.readthedocs.io/)
- [PHP Mock 对象](https://phpunit.readthedocs.io/en/9.5/test-doubles.html)
- [测试最佳实践](https://phpunit.readthedocs.io/en/9.5/writing-tests-for-phpunit.html)

---

Happy Testing! 🧪✨
