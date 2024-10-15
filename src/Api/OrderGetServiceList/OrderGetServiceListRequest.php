<?php
namespace DouDianSDK\Api\OrderGetServiceList;

//auto generated code
class OrderGetServiceListRequest
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
		return "/order/getServiceList";
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
