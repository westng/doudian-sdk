<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Core\Validator;

use DouDianSdk\Core\Exception\DouDianException;

/**
 * 参数验证器.
 */
class Validator
{
    /**
     * 验证规则.
     *
     * @var array
     */
    private $rules = [];

    /**
     * 验证数据.
     *
     * @var array
     */
    private $data = [];

    /**
     * 错误信息.
     *
     * @var array
     */
    private $errors = [];

    /**
     * 构造函数.
     *
     * @param array $data 要验证的数据
     * @param array $rules 验证规则
     */
    public function __construct(array $data = [], array $rules = [])
    {
        $this->data  = $data;
        $this->rules = $rules;
    }

    /**
     * 设置验证数据.
     *
     * @param array $data 验证数据
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * 设置验证规则.
     *
     * @param array $rules 验证规则
     */
    public function setRules(array $rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * 添加验证规则.
     *
     * @param string $field 字段名
     * @param string $rule 验证规则
     * @param string $message 错误消息
     */
    public function addRule($field, $rule, $message = ''): self
    {
        if (!isset($this->rules[$field])) {
            $this->rules[$field] = [];
        }

        $this->rules[$field][] = [
            'rule'    => $rule,
            'message' => $message,
        ];

        return $this;
    }

    /**
     * 执行验证
     *
     * @return bool 验证是否通过
     *
     * @throws DouDianException 验证失败时抛出异常
     */
    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            foreach ($fieldRules as $ruleConfig) {
                $rule    = $ruleConfig['rule'];
                $message = $ruleConfig['message'];

                if (!$this->validateField($field, $rule, $message)) {
                    break; // 一个字段的第一个验证失败就跳出
                }
            }
        }

        if (!empty($this->errors)) {
            throw new DouDianException('Validation failed: ' . implode(', ', $this->errors));
        }

        return true;
    }

    /**
     * 验证单个字段.
     *
     * @param string $field 字段名
     * @param string $rule 验证规则
     * @param string $message 错误消息
     */
    private function validateField($field, $rule, $message): bool
    {
        $value     = $this->data[$field] ?? null;
        $ruleParts = explode(':', $rule);
        $ruleName  = $ruleParts[0];
        $ruleParam = $ruleParts[1] ?? null;

        switch ($ruleName) {
            case 'required':
                if (empty($value) && 0 !== $value) {
                    $this->errors[] = $message ?: "Field '{$field}' is required";

                    return false;
                }
                break;

            case 'string':
                if (!is_string($value)) {
                    $this->errors[] = $message ?: "Field '{$field}' must be a string";

                    return false;
                }
                break;

            case 'integer':
                if (!is_int($value) && !ctype_digit($value)) {
                    $this->errors[] = $message ?: "Field '{$field}' must be an integer";

                    return false;
                }
                break;

            case 'numeric':
                if (!is_numeric($value)) {
                    $this->errors[] = $message ?: "Field '{$field}' must be numeric";

                    return false;
                }
                break;

            case 'min':
                if ($value < $ruleParam) {
                    $this->errors[] = $message ?: "Field '{$field}' must be at least {$ruleParam}";

                    return false;
                }
                break;

            case 'max':
                if ($value > $ruleParam) {
                    $this->errors[] = $message ?: "Field '{$field}' must be no more than {$ruleParam}";

                    return false;
                }
                break;

            case 'min_length':
                if (strlen($value) < $ruleParam) {
                    $this->errors[] = $message ?: "Field '{$field}' must be at least {$ruleParam} characters";

                    return false;
                }
                break;

            case 'max_length':
                if (strlen($value) > $ruleParam) {
                    $this->errors[] = $message ?: "Field '{$field}' must be no more than {$ruleParam} characters";

                    return false;
                }
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[] = $message ?: "Field '{$field}' must be a valid email";

                    return false;
                }
                break;

            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->errors[] = $message ?: "Field '{$field}' must be a valid URL";

                    return false;
                }
                break;

            case 'in':
                $allowedValues = explode(',', $ruleParam);

                if (!in_array($value, $allowedValues)) {
                    $this->errors[] = $message ?: "Field '{$field}' must be one of: {$ruleParam}";

                    return false;
                }
                break;

            case 'array':
                if (!is_array($value)) {
                    $this->errors[] = $message ?: "Field '{$field}' must be an array";

                    return false;
                }
                break;

            case 'date':
                if (!$this->isValidDate($value)) {
                    $this->errors[] = $message ?: "Field '{$field}' must be a valid date";

                    return false;
                }
                break;

            default:
                // 自定义验证规则
                if (method_exists($this, 'validate' . ucfirst($ruleName))) {
                    $method = 'validate' . ucfirst($ruleName);

                    if (!$this->$method($field, $value, $ruleParam, $message)) {
                        return false;
                    }
                }
                break;
        }

        return true;
    }

    /**
     * 验证日期格式.
     *
     * @param string $date 日期字符串
     */
    private function isValidDate($date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d H:i:s', $date);

        return $d && $d->format('Y-m-d H:i:s') === $date;
    }

    /**
     * 获取错误信息.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * 检查是否有错误.
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * 清除错误.
     */
    public function clearErrors(): self
    {
        $this->errors = [];

        return $this;
    }
}
