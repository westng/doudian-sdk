<?php
namespace DouDianSdk\Api\order_getMCToken;

//auto generated code
class OrderGetMCTokenRequest
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
		return "/order/getMCToken";
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
