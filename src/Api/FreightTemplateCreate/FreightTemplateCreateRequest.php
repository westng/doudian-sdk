<?php

namespace DouDianSdk\Api\FreightTemplateCreate;

//auto generated code
class FreightTemplateCreateRequest
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
        return "/freightTemplate/create";
    }

    public function execute($accessToken)
    {
        return \DouDianSdk\Core\DoudianOpClient::getInstance()->request($this, $accessToken);
    }

    public function __construct()
    {
        $this->config = \DouDianSdk\Core\GlobalConfig::getGlobalConfig();
    }
}
