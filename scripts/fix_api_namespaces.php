<?php

declare(strict_types=1);

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

$root = 'src/Api';

foreach ($argv as $arg) {
    if (0 === strpos($arg, '--root=')) {
        $root = substr($arg, 7);
    }
}

if (!is_dir($root)) {
    fwrite(STDERR, "Directory not found: {$root}\n");
    exit(1);
}

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

$changed = 0;
$scanned = 0;

foreach ($files as $fileInfo) {
    /** @var SplFileInfo $fileInfo */
    if (!$fileInfo->isFile() || 'php' !== strtolower($fileInfo->getExtension())) {
        continue;
    }

    $path = $fileInfo->getPathname();
    $code = file_get_contents($path);

    if (false === $code) {
        continue;
    }

    ++$scanned;
    $original = $code;

    if (!preg_match('/^\s*namespace\s+[^;]+;/m', $code)) {
        $relativeDir   = trim(str_replace('\\', '/', substr($fileInfo->getPath(), strlen($root))), '/');
        $namespace     = 'DouDianSdk\\Api' . ('' !== $relativeDir ? '\\' . str_replace('/', '\\', $relativeDir) : '');
        $namespaceLine = "namespace {$namespace};\n\n";

        if (preg_match('/\n(class|interface|trait|enum)\s+[A-Za-z_][A-Za-z0-9_]*/', $code, $m, PREG_OFFSET_CAPTURE)) {
            $pos  = $m[0][1];
            $code = substr($code, 0, $pos) . "\n" . $namespaceLine . substr($code, $pos);
        } else {
            $code = rtrim($code) . "\n\n" . $namespaceLine;
        }
    }

    $code = preg_replace(
        '/(?<![\\\\A-Za-z0-9_])DouDianOpClient::getInstance\(\)/',
        '\\\\DouDianSdk\\\\Core\\\\Client\\\\DouDianOpClient::getInstance()',
        $code
    );
    $code = preg_replace(
        '/(?<![\\\\A-Za-z0-9_])GlobalConfig::getGlobalConfig\(\)/',
        '\\\\DouDianSdk\\\\Core\\\\Config\\\\GlobalConfig::getGlobalConfig()',
        $code
    );

    if ($code !== $original) {
        file_put_contents($path, $code);
        ++$changed;
        echo "fixed: {$path}\n";
    }
}

echo "done. scanned={$scanned}, changed={$changed}\n";
