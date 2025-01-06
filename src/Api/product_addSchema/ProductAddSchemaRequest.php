<?php

namespace DouDianSdk\Api\product_addSchema;

//auto generated code
class ProductAddSchemaRequest
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
        return "/product/addSchema";
    }

    public function execute($accessToken)
    {
        return \DouDianSdk\Core\DouDianOpClient::getInstance()->request($this, $accessToken);
    }

    public function __construct()
    {
        $this->config = \DouDianSdk\Core\GlobalConfig::getGlobalConfig();
    }
}
