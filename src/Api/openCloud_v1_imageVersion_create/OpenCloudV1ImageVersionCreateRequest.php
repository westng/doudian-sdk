<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Api\openCloud_v1_imageVersion_create;

// auto generated code
class OpenCloudV1ImageVersionCreateRequest
{
    private $param;

    private $config;

    public function setParam($param)
    {
        $this->param = $param;
    }

    public function getParam()
    {
        return $this->param;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getUrlPath()
    {
        return '/openCloud/v1/imageVersion/create';
    }

    public function execute($accessToken)
    {
        return \DouDianSdk\Core\Client\DouDianOpClient::getInstance()->request($this, $accessToken);
    }

    public function __construct()
    {
        $this->config = \DouDianSdk\Core\Config\GlobalConfig::getGlobalConfig();
    }
}
