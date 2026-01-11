# Hyperf 3.x 兼容性补丁

## 问题描述

Hyperf 3.x 的 `PoolHandler` 构造函数签名发生了变化：

- **Hyperf 2.x**: `new PoolHandler(array $options)`
- **Hyperf 3.x**: `new PoolHandler(PoolFactory $factory, array $options = [])`

SDK 原来直接传数组，在 Hyperf 3.x 环境下会报错。

## 解决方案

### 方案 1：直接使用修复后的 SDK（推荐）

如果你使用的是本仓库的最新代码，已经包含了兼容性修复，无需额外操作。

### 方案 2：使用 Composer Patches（适用于通过 Composer 安装的场景）

如果你通过 `composer require westng/doudian-sdk` 安装，`composer update` 会覆盖修改。
使用 `cweagans/composer-patches` 可以持久化补丁。

#### 步骤 1：安装 composer-patches

```bash
composer require cweagans/composer-patches
```

#### 步骤 2：配置 composer.json

在项目的 `composer.json` 中添加：

```json
{
    "extra": {
        "patches": {
            "westng/doudian-sdk": {
                "Hyperf 3.x PoolHandler 兼容性修复": "patches/doudian-sdk-hyperf3-fix.patch"
            }
        }
    }
}
```

#### 步骤 3：复制补丁文件

将 `patches/doudian-sdk-hyperf3-fix.patch` 复制到你项目的 `patches/` 目录。

#### 步骤 4：重新安装依赖

```bash
composer install
```

## 修复内容

补丁新增了 `createPoolHandler()` 方法，自动检测 Hyperf 版本：

1. 检测是否存在 Hyperf 3.x 的 `PoolFactory` 类
2. 如果是 3.x，从容器获取 `PoolFactory` 实例并传入
3. 如果获取失败或是 2.x，回退到直接传数组的方式

这样可以同时兼容 Hyperf 2.x 和 3.x。
