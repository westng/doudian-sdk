<?php

namespace DouDianSDK\Api\AddressAreaList;

//auto generated code
class AddressAreaListRequest
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
		return "/address/areaList";
	}

	public function execute($accessToken)
	{
		return \DouDianSDK\Core\DoudianOpClient::getInstance()->request($this, $accessToken);
	}

	public function __construct()
	{
		$this->config = \DouDianSDK\Core\GlobalConfig::getGlobalConfig();
	}
}
