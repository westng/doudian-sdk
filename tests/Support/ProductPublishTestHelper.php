<?php

namespace DouDianSdk\Tests\Support;

final class ProductPublishTestHelper
{
    public static function loadJsonPayload(string $defaultFile, string $envJson = '', string $envFile = ''): array
    {
        $payloadRaw = trim($envJson);

        if ('' === $payloadRaw) {
            $candidateFiles = [];

            if ('' !== trim($envFile)) {
                $candidateFiles[] = trim($envFile);
            }

            $candidateFiles[] = $defaultFile;

            foreach ($candidateFiles as $payloadFile) {
                $resolvedFile = self::resolvePath($payloadFile);

                if (is_file($resolvedFile)) {
                    $payloadRaw = trim((string) file_get_contents($resolvedFile));
                    break;
                }
            }
        }

        if ('' === $payloadRaw) {
            return [];
        }

        $payload = json_decode($payloadRaw, true);

        return is_array($payload) ? $payload : [];
    }

    public static function buildRecommendCategoryPayload(array $recommendPayload, array $productPayload, string $fallbackName, string $sceneOverride = ''): array
    {
        $scene = trim($sceneOverride);

        if ('' === $scene) {
            $scene = trim((string) ($recommendPayload['scene'] ?? 'category_infer'));
        }
        if ('' === $scene) {
            $scene = 'category_infer';
        }

        $requestData = [
            'scene' => $scene,
            'name'  => (string) ($recommendPayload['name'] ?? $productPayload['name'] ?? $fallbackName),
        ];

        if (!empty($recommendPayload['async_task_id'])) {
            $requestData['async_task_id'] = (string) $recommendPayload['async_task_id'];
        }

        if (!empty($recommendPayload['standard_brand_id'])) {
            $requestData['standard_brand_id'] = (int) $recommendPayload['standard_brand_id'];
        }

        if (isset($recommendPayload['product_format_new']) && is_array($recommendPayload['product_format_new']) && [] !== $recommendPayload['product_format_new']) {
            $requestData['product_format_new'] = $recommendPayload['product_format_new'];
        }

        if ('predict_by_title_and_img' === $scene && isset($recommendPayload['pic'])) {
            $requestData['pic'] = self::normalizeRecommendCategoryPics($recommendPayload['pic']);
        }

        return $requestData;
    }

    public static function normalizeMediaString($value): string
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (!is_array($value)) {
            return '';
        }

        $urls = [];
        $isList = array_keys($value) === range(0, count($value) - 1);

        if (!$isList && isset($value['url'])) {
            return trim((string) $value['url']);
        }

        foreach ($value as $item) {
            if (is_string($item) && '' !== trim($item)) {
                $urls[] = trim($item);
                continue;
            }

            if (is_array($item) && isset($item['url']) && '' !== trim((string) $item['url'])) {
                $urls[] = trim((string) $item['url']);
            }
        }

        return implode('|', $urls);
    }

    public static function normalizeRecommendCategoryPics($value): array
    {
        if (is_string($value)) {
            $urls = array_filter(array_map('trim', explode('|', $value)), static function ($item) {
                return '' !== $item;
            });

            return array_map(static function ($url) {
                return ['url' => $url];
            }, array_values($urls));
        }

        if (!is_array($value)) {
            return [];
        }

        $isList = array_keys($value) === range(0, count($value) - 1);

        if (!$isList && isset($value['url']) && '' !== trim((string) $value['url'])) {
            return [['url' => trim((string) $value['url'])]];
        }

        $ret = [];

        foreach ($value as $item) {
            if (is_string($item) && '' !== trim($item)) {
                $ret[] = ['url' => trim($item)];
                continue;
            }

            if (is_array($item) && isset($item['url']) && '' !== trim((string) $item['url'])) {
                $ret[] = ['url' => trim((string) $item['url'])];
            }
        }

        return $ret;
    }

    public static function isApiSuccess(array $result): bool
    {
        if (array_key_exists('err_no', $result)) {
            return 0 === (int) $result['err_no'];
        }

        if (array_key_exists('code', $result)) {
            return 10000 === (int) $result['code'];
        }

        return false;
    }

    public static function getApiCode(array $result): ?int
    {
        if (array_key_exists('err_no', $result) && null !== $result['err_no']) {
            return (int) $result['err_no'];
        }

        if (array_key_exists('code', $result) && null !== $result['code']) {
            return (int) $result['code'];
        }

        if (isset($result['data']) && is_array($result['data'])) {
            if (array_key_exists('err_no', $result['data']) && null !== $result['data']['err_no']) {
                return (int) $result['data']['err_no'];
            }

            if (array_key_exists('code', $result['data']) && null !== $result['data']['code']) {
                return (int) $result['data']['code'];
            }
        }

        return null;
    }

    public static function getApiMessage(array $result): ?string
    {
        if (isset($result['message'])) {
            return (string) $result['message'];
        }

        if (isset($result['msg'])) {
            return (string) $result['msg'];
        }

        if (isset($result['data']) && is_array($result['data'])) {
            if (isset($result['data']['message'])) {
                return (string) $result['data']['message'];
            }

            if (isset($result['data']['msg'])) {
                return (string) $result['data']['msg'];
            }
        }

        return null;
    }

    public static function extractRecommendIds($node): array
    {
        $ids = [];
        self::walkRecommendIds($node, $ids);

        $uniq = [];

        foreach ($ids as $id) {
            $key = (string) $id;

            if ('' === $key || isset($uniq[$key])) {
                continue;
            }

            $uniq[$key] = true;
        }

        return array_keys($uniq);
    }

    public static function normalizeIdList($ids): array
    {
        if (!is_array($ids)) {
            return [];
        }

        $vals = [];

        foreach ($ids as $id) {
            if (is_scalar($id) && '' !== trim((string) $id)) {
                $vals[trim((string) $id)] = true;
            }
        }

        $ret = array_keys($vals);
        sort($ret, SORT_STRING);

        return $ret;
    }

    private static function walkRecommendIds($node, array &$ids): void
    {
        if (!is_array($node)) {
            return;
        }

        foreach ($node as $key => $value) {
            if ('recommend_id' === $key || 'recommendId' === $key) {
                if (is_array($value)) {
                    foreach ($value as $item) {
                        if (is_scalar($item)) {
                            $ids[] = $item;
                        }
                    }
                } elseif (is_scalar($value)) {
                    $ids[] = $value;
                }
            }

            if (is_array($value)) {
                self::walkRecommendIds($value, $ids);
            }
        }
    }

    private static function resolvePath(string $path): string
    {
        if ('/' === substr($path, 0, 1)) {
            return $path;
        }

        return dirname(__DIR__, 2) . '/' . $path;
    }
}
