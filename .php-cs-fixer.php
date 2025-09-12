<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/examples',
    ])
    ->exclude([
        'vendor',
        'coverage',
        'cache',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        // 基础规则集
        '@PSR12'   => true,
        '@Symfony' => true,

        // 自定义规则
        'array_syntax'           => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'operators' => [
                '=>' => 'align_single_space_minimal',
                '='  => 'align_single_space_minimal',
            ],
        ],
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'try', 'if', 'switch', 'for', 'foreach', 'do', 'while'],
        ],
        'class_attributes_separation' => [
            'elements' => [
                'method'   => 'one',
                'property' => 'one',
            ],
        ],
        'concat_space'          => ['spacing' => 'one'],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'no_unused_imports' => true,
        'ordered_imports'   => [
            'sort_algorithm' => 'alpha',
            'imports_order'  => ['class', 'function', 'const'],
        ],
        'phpdoc_align'                => ['align' => 'left'],
        'phpdoc_indent'               => true,
        'phpdoc_no_access'            => true,
        'phpdoc_no_package'           => true,
        'phpdoc_summary'              => true,
        'phpdoc_trim'                 => true,
        'phpdoc_types'                => true,
        'single_quote'                => true,
        'trailing_comma_in_multiline' => true,
        'visibility_required'         => ['elements' => ['method', 'property']],
        'header_comment'              => [
            'header' => <<<'EOF'
This file is part of DouDian-SDK

@link     https://github.com/westng/doudian-sdk-php
@document https://github.com/westng/doudian-sdk/blob/main/README.md
@contact  457395070@qq.com
@license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
EOF,
            'comment_type' => 'PHPDoc',
            'location'     => 'after_declare_strict',
            'separate'     => 'both',
        ],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache')
    ->setUsingCache(true);
