<?php
/**
 * Demo 运行
 * 1.设备注册
 * 2.设备上报
 */
// 平台支持connect、subscribe、publish、ping、unsubscribe、disconnect等报文
// 不支持pubrec、pubrel、pubcomp报文
// 特性支持
// 平台对协议特性支持如下：

// 特性  是否支持    说明
// will    不支持 will、will retain 的flag必须为0，will qos必须为0
// session 不支持 cleansession标记必须为1
// retain  不支持 相关标记必须为0
// QoS0    支持  设备由订阅成功而收到的系统 topic 的消息均为 QoS0
// 设备发布至平台系统 topic 的消息均支持 QoS0
// QoS1    支持  设备发布至平台系统 topic 的消息均支持 QoS1
// QoS2    不支持

use Onenet\DeviceRegister;
use Onenet\DeviceReport;
require_once __DIR__ . '/DeviceRegister.php'; 
require_once __DIR__ . '/DeviceReport.php'; 
/*1.设备注册*/
$deviceRegister = new DeviceRegister();
$mqtt = $deviceRegister->initMqttClient();