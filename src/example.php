<?php

require "autoload.php";

include "open/api/materialgw/BinaryMaterialUploadAddressAuthParam.php";
include "open/api/materialgw/BinaryMaterialUploadAddressAuthRequest.php";
include "open/api/materialgw/BinaryMaterialUploadParam.php";



// ====API使用示例====
// $accessToken = AccessTokenBuilder::build("xxxx", ACCESS_TOKEN_SHOP_ID);
// 创建Request对象，假设调用的方法名称是: demo.method
// $request= new DemoMethodRequest();
// 发起API调用
// $response = $request->execute(accessToken);

// ====SPI使用示例====
// 服务端调用spi接口时的链接参数
// $request = new DoudianOpSpiRequest();
// $param = $request->getSpiParam();
// $param->appKey = 'xxxxx';
// $param->paramJson = "{}";
// $param->sign = 'xxxxxxx';
// $param->signMethod = 'md5';
// $param->timestamp = '2006-01-02 15:04:05';
// 定义并注册一个spi处理器
// $request->registerHandler(function ($context) {
//    $paramJsonObj = $context->getParamJsonObject();
//    var_dump($paramJsonObj);
//
//    echo $paramJsonObj->order_id;
//    echo $paramJsonObj->to_receiver_info->post_tel;
//
//    $data = array();
//    $data["order_id"] = "$paramJsonObj->order_id";
//    $data["shop_id"] = "$paramJsonObj->shop_id";
//    $context->setResponseData($data);
//    $context->wrapSuccess();
// });
// 执行处理器
// $response = $request->execute();
// 将response返回给服务器
// write return code here