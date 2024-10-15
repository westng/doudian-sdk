<?php
namespace DouDianSDK\Api\MaterialCreateFolder;

//auto generated code
class MaterialCreateFolderRequest
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
		return "/material/createFolder";
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
