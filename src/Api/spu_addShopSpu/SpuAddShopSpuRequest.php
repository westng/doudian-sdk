<?php

namespace DouDianSdk\Api\spu_addShopSpu;

//auto generated code
class SpuAddShopSpuRequest
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
        return "/spu/addShopSpu";
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