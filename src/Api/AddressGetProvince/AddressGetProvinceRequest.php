<?php
namespace DouDianSDK\Api\AddressGetProvince;

//auto generated code
class AddressGetProvinceRequest
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
		return "/address/getProvince";
	}

	public function execute($accessToken)
	{
		return \DouDianSDKre\DoudianOpClient::getInstance()->request($this, $accessToken);
	}

	public function __construct()
	{
		$this->config = \DouDianSDKre\GlobalConfig::getGlobalConfig();
	}
}
