# 抖店SDK Makefile

.PHONY: help install test test-coverage cs-check cs-fix phpstan clean

# 默认目标
help: ## 显示帮助信息
	@echo "抖店SDK 开发命令"
	@echo "=================="
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

install: ## 安装依赖
	composer install

install-prod: ## 安装生产依赖
	composer install --no-dev

install-dev: ## 安装开发依赖
	composer install --dev

test: ## 运行测试（单元测试+演示测试）
	composer test

test-unit: ## 运行单元测试
	composer test-unit

test-integration: ## 运行集成测试（需要设置环境变量）
	composer test-integration

test-demo: ## 运行演示测试
	composer test-demo

test-all: ## 运行所有测试
	composer test-all

test-coverage: ## 运行测试并生成覆盖率报告
	composer test-coverage

cs-check: ## 检查代码风格 (PHP_CodeSniffer)
	composer cs-check

cs-fix: ## 修复代码风格问题 (PHP_CodeSniffer)
	composer cs-fix

cs-fixer-check: ## 检查代码风格 (PHP CS Fixer)
	composer cs-fixer-check

cs-fixer-fix: ## 修复代码风格 (PHP CS Fixer)
	composer cs-fixer-fix

style-check: cs-check cs-fixer-check ## 检查所有代码风格工具

style-fix: cs-fix cs-fixer-fix ## 修复所有代码风格工具

phpstan: ## 运行静态分析
	composer phpstan

lint: ## 运行所有代码检查
	@echo "运行代码风格检查..."
	composer style-check
	@echo "运行静态分析..."
	composer phpstan

clean: ## 清理临时文件
	rm -rf coverage/
	rm -rf vendor/
	rm -rf composer.lock

update: ## 更新依赖
	composer update

validate: ## 验证composer配置
	composer validate

security-check: ## 检查安全漏洞
	composer audit

docs: ## 生成文档
	@echo "文档位于 README.md 和 examples/ 目录"

examples: ## 运行示例
	@echo "基础示例:"
	@echo "php examples/basic_usage.php"
	@echo ""
	@echo "高级示例:"
	@echo "php examples/advanced_usage.php"

# 开发环境设置
dev-setup: install-dev ## 设置开发环境
	@echo "开发环境设置完成！"
	@echo "运行 'make test' 来验证设置"

# CI/CD 命令
ci: lint test ## CI/CD 流水线命令

# 发布准备
release-check: lint test-coverage ## 发布前检查
	@echo "所有检查通过，可以发布！"
