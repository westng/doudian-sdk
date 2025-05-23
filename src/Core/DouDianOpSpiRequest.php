<?php

namespace DouDianSdk\Core;

class DouDianOpSpiRequest
{
    private $spiParam;

    private $config;

    private $spiClient;

    private $bizHandler;

    public function execute()
    {
        return $this->spiClient->request($this, $this->bizHandler);
    }

    public function registerHandler($bizHandler)
    {
        $this->bizHandler = $bizHandler;
    }

    public function __construct()
    {
        $this->config = \DouDianSdk\Core\GlobalConfig::getGlobalConfig();
        $this->spiClient = DouDianOpSpiClient::getInstance();
        $this->spiParam = new DouDianOpSpiParam();
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getSpiParam()
    {
        return $this->spiParam;
    }

    public function getSpiClient()
    {
        return $this->spiClient;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function setSpiClient($spiClient)
    {
        $this->spiClient = $spiClient;
    }

    public function setSpiParam($spiParam)
    {
        $this->spiParam = $spiParam;
    }
}
