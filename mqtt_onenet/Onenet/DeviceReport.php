<?php
namespace Onenet;
use TrytoMqtt\Client;
require_once __DIR__ . '/../autoload.php';

require_once __DIR__ . '/DeviceUser.php';
require_once __DIR__ . '/OnenetConfig.php';
require_once __DIR__ . '/OnenetMessage.php';
require_once __DIR__ . '/TlvRespMessage.php';
require_once __DIR__ . '/DeviceRegister.php';

use Onenet\DeviceUser;
use Onenet\OnenetConfig;
use Onenet\OnenetMessage;
use Onenet\TlvRespMessage;
use Onenet\DeviceRegister;

/**
* OneNet的设备上报
* @author jing
* @date 2020/06/18
*/
class DeviceReport {
	//  连接地址
    /**
     * @var string
     */
    public $host = "";

    /**
     * @var int
     */
    public $port = 0;
    //
    /**
     * 平台分配的产品ID
     * @var string
     */
    public $productId = "";
    //
    /**
     * token
     * @var string
     */
    public $token = "";
    //  设备sn
    //
    /**
     *
     * 设备SN
     * @var string
     */
    public $clientId = "";
    //  topIC thing/products/产品id/devices/设备sn/command_resp
    //  
    //  
    /**
     * @var string
     */
    public $pub_topic = '';
    /**
     * @var string
     */
    public $sub_topic = '';

    /**
     * @var string
     */
    public $up_topic = '';

    /**
     * @var mixed
     */
    public $message = NULL;

    /**
     * @var mixed
     */
    public $tlvRespMessage = NULL;

    /**
     * @var int
     */
    public $timer_id = 0;

    /**
     * @var array
     */
    public $queue_list = [];


    public $deviceUser = null;

    public function __construct() {
        $this->message = new OnenetMessage();
        $this->tlvRespMessage = new TlvRespMessage();
        $this->deviceUser = new DeviceUser();
        $this->clientId = $this->deviceUser->getSn();
        $this->productId = $this->deviceUser->getProductId();
        $this->getReportCfg();
    }

    /**
     * 获取接入机器配置信息
     * [getReportCfg description]
     * @return [type] [description]
     */
    public function getReportCfg(){
    	$arr = DeviceRegister::getReportJsonInfo();
        // var_dump($arr);
        $this->host = isset($arr['reportIp']) ? $arr['reportIp'] : '';
        $this->port = isset($arr['reportPort']) ? $arr['reportPort'] : 0;
        $this->token = isset($arr['token']) ? $arr['token'] : '';
    }

    /**
     * @return mixed
     */
    public function getHostAddr() {
        return $this->host;
    }

    /**
     * @return mixed
     */
    public function getHostPort() {
        return $this->port;
    }

    /**
     * @return mixed
     */
    public function getClientId() {
        return $this->clientId;
    }

    /**
     * @return mixed
     */
    public function getPassWord() {
        return $this->token;
    }

    /**
     * @return mixed
     */
    public function getUserName() {
        return $this->productId;
    }

    /**
     * [getPublicTopic description]
     * @return [type] [description]
     */
    public function getPublicTopic() {
        $this->pub_topic = "thing/products/" . $this->productId . "/devices/" . $this->clientId . "/command";
        return $this->pub_topic;
    }

    /**
     * [setTimerId description]
     * @param [type] $timer_id [description]
     */
    public function setTimerId($timer_id) {
        $this->timer_id = $timer_id;
    }

    /**
     * [getTimerId description]
     * @return [type] [description]
     */
    public function getTimerId() {
        return $timer_id;
    }

    /**
     * @return mixed
     */
    public function getMissionEle() {
        $ele = -1;
        if (!empty($this->queue_list)) {
            $ele = $this->queue_list[0];
            $this->shiftEle();
        }
        return $ele;
    }

    /**
     * [pushEle description]
     * @return [type] [description]
     */
    public function pushEle($value) {
        array_push($this->queue_list, $value);
    }

    /**
     * [shiftEle description]
     * @return [type] [description]
     */
    public function shiftEle() {
        array_shift($this->queue_list);
    }

    /**
     *
     * [getSubTopic description]
     * @return [type] [description]
     */
    public function getSubTopic() {
        $this->sub_topic = "thing/products/" . $this->productId . "/devices/" . $this->clientId . "/command_resp";
        return $this->sub_topic;
    }

    /**
     * @return mixed
     */
    public function getUpReportTopic() {
        $this->up_topic = '$sys/' . $this->productId . "/" . $this->clientId . "/thing/tlv/up";
        return $this->up_topic;
    }

    public function gerUpErrResponseTopic() {
        return '$sys/' . $this->productId . "/" . $this->clientId . "/thing/tlv/up/rejected";
    }

    /**
     * [gerUpSuccResponseTopic description]
     * @return [type] [description]
     */
    public function gerUpSuccResponseTopic() {
        return '$sys/' . $this->productId . "/" . $this->clientId . "/thing/tlv/up/accepted";
    }

    // Topic约定
    // 平台对 topic 约定如下：

    // 暂不支持用户自定义 topic，仅限使用系统 topic
    // 系统topic均为 $ 开头
    // 用户可以使用相关系统 topic 访问接入套件中的存储、命令等服务，详情请见 topic簇
    // 设备使用 系统 topic 时暂仅限订阅与发布消息至自己相关的 topic，不能跨设备/产品订阅与发布
    // topic非法使用

    // 设备订阅非法 topic 时，平台通过MQTT publish ack返回订阅失败

    // 设备发布消息到非法topic时，平台会断开设备连接

    // 通配符：平台支持通配符 + 与 #

    // 说明：使用 系统topic 时支持的通配符仅支持从 topic 第四级目录开始

    // 支持 $sys/{pid}/{device-name}/#、$sys/{pid}/{device-name}/cmd/#、$sys/{pid}/{device-name}/cmd/request/+等订阅方式
    // 不支持 $sys/{pid}/#、$sys/#、# 等订阅方式
    // 订阅 topic    订阅效果
    // $sys/{pid}/{device-name}/dp/post/json/accepted  订阅设备数据点上报成功的消息
    // $sys/{pid}/{device-name}/dp/post/json/rejected  订阅设备数据点上报失败的消息
    // $sys/{pid}/{device-name}/dp/post/json/+ 订阅设备数据点上报结果
    // $sys/{pid}/{device-name}/cmd/request/+  订阅设备所有命令消息
    // $sys/{pid}/{device-name}/cmd/response/+/+   订阅设备所有命令应答结果消息
    // $sys/{pid}/{device-name}/cmd/#  订阅设备所有命令相关消息
    // $sys/{pid}/{device-name}/#  订阅设备所有相关消息
    //

    /**
     * 十六进制转字符串
     * [hexToStr description]
     * @param  [type] $hex [description]
     * @return [type]      [description]
     */
    public function hexToStr($hex) {
        $str = "";
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $str .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }

        return $str;
    }

    /**
     * 字符串转十六进制
     * [strToHex description]
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    public function strToHex($str) {
        $hex = "";
        for ($i = 0; $i < strlen($str); $i++) {
            $hex .= dechex(ord($str[$i]));
        }

        // $hex=strtoupper($hex);
        return $hex;
    }

    /**
     * 字符串转二进制
     * [StrToBin description]
     * @param [type] $str [description]
     */
    public function StrToBin($str) {
        //1.列出每个字符
        $arr = preg_split('/(?<!^)(?!$)/u', $str);
        //2.unpack字符
        foreach ($arr as &$v) {
            $temp = unpack('H*', $v);
            $v = base_convert($temp[1], 16, 2);
            unset($temp);
        }

        return join(' ', $arr);
    }

    /**
     * 将二进制转换成字符串
     * @param type $str
     * @return type
     */
    public function BinToStr($str) {
        $arr = explode(' ', $str);
        foreach ($arr as &$v) {
            $v = pack("H" . strlen(base_convert($v, 2, 16)), base_convert($v, 2, 16));
        }
        return join('', $arr);
    }

    /**
     * 设备上报消息
     * [devUpReportMessage description]
     * @return [type] [description]
     */
    public function devUpReportMessage() {
        $this->message->setVersion(1); //版本 1
        $this->message->setMsgId(1); //消息id 1
        $options = [
            'options_type' => Onenetconfig::OPTIONS_TYPE_REQUEST, //请求 常量
            'options_dev' => Onenetconfig::OPTIONS_DEV_GATE, //设备 常量
            'options_target' => Onenetconfig::OPTIONS_TARGET_RESOURCE, //系统 常量
            'options_act' => Onenetconfig::OPTIONS_ACT_NOTIFY, // 常量
        ];
        $this->message->setOptions($options);
        $func_list = [
            [
                // 'func_id' => [
                //     'data_type' => Onenetconfig::FUNCTION_DATA_FLOAT, //数据类型 常量
                //     'func_type' => Onenetconfig::FUNCTION_TYPE_CUSTOM, //功能类型 常量
                //     'resouce_id' => 28928,
                // ],
                'custom_func_id' => 28928,
                'func_value' => 23, //温度
            ],
            [
                'custom_func_id' => 61697,
                'func_value' => 'error', //错误提示
            ],
            [
                'custom_func_id' => 28930,
                'func_value' => 11, //地点
            ],

        ];
        $this->message->setFunctionList($func_list);
        $content = $this->message->encode();
        return $content;
    }

    /**
     * 返回上报响应的消息体
     * [decodeRespMess description]
     * @param  [type] $hex_str [description]
     * @return [type]          [description]
     */
    public function decodeRespMess($hex_str) {
        return $this->tlvRespMessage->decode($hex_str);
    }


    /**
     * 初始化上报客户端
     * [initReportMqttCli description]
     * @return [type] [description]
     */
    public function initReportMqttCli(){
		$options = [
		    'debug' => 0,
		    'keepalive' => 1800,
		    'reconnect_period' => 0,
		    'clean_session' => 1, //设置clean_session为True表示要建立一个非持久性的会话
		    'client_id' => $this->getClientId(),
		    'username' => $this->getUserName(),
		    'password' => $this->getPassWord(),
		];
		// var_dump($options,$this->getHostAddr(), $this->getHostPort());
		$mqtt = new Client($this->getHostAddr(), $this->getHostPort(), $options);
		$mqtt->onConnect = function ($mqtt)  {
		    $mqtt->subscribe($this->gerUpErrResponseTopic());
		    $mqtt->subscribe($this->gerUpSuccResponseTopic());
		    /*上传数据*/
		    $reg_data = $this->devUpReportMessage(); // '810000000122e8010c6d5a6442353771434d6c6e4a70b6';
		    // var_dump($reg_data);
		    // var_dump($this->getUpReportTopic());
		    // exit;
		    $mqtt->publish($this->getUpReportTopic(), hex2bin($reg_data));
		    //接收成功
		    swoole_timer_tick(60000, function ($timer_id) use ($mqtt) {
		        $ele = $this->getMissionEle();
		        if (!empty($ele) && $ele > 0) {
		            $reg_data = $this->devUpReportMessage(); //
		            var_dump("report data:" . $reg_data);
		            $mqtt->publish($this->getUpReportTopic(), hex2bin($reg_data));
		        }
		    });
		};
		/*消息响应*/
		$mqtt->onMessage = function ($topic, $content)  {
		    $hex_str = bin2hex($content);
		    $resp_info = $this->decodeRespMess($hex_str);
		    // var_dump($topic, $hex_str, $resp_info);
		    if ($topic == $this->gerUpSuccResponseTopic()) {
		        if (isset($resp_info['err_code']) && $resp_info['err_code'] == 200) {
		        	var_dump(date('Y-m-d H:i:s')." 上报成功....");
		            $this->pushEle(1);
		            // var_dump($this->queue_list);
		        }
		    }

		};

		$mqtt->onError = function ($exception) {
		    echo "error\n";
		};
		$mqtt->onClose = function () {
		    echo "close\n";
		};
		$mqtt->connect();
    }
}