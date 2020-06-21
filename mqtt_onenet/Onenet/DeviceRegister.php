<?php
namespace Onenet;
use TrytoMqtt\Client;
require_once __DIR__ . '/../autoload.php';

require_once __DIR__ . '/DeviceUser.php';
require_once __DIR__ . '/OnenetConfig.php';
require_once __DIR__ . '/OnenetMessage.php';
require_once __DIR__ . '/TlvRespMessage.php';

use Onenet\DeviceUser;
use Onenet\OnenetConfig;
use Onenet\OnenetMessage;
use Onenet\TlvRespMessage;

/**
* OneNet的设备注册
* @author jing
* @date 2020/06/18
*/
class DeviceRegister {
	
   //  注册连接地址
    /**
     * @var string
     */
    public $host = "183.230.40.32";

    /**
     * 注册端口
     * @var int
     */
    public $port = 26003;
    //
    /**
     * 平台分配的产品ID
     * @var string
     */
    public $productId = "";
    //
    /**
     * 设备注册码
     * 
     */
    public $devRegCode = "";
    //  设备sn
    //
    /**
     *
     * 设备SN
     * @var string
     */
    public $clientId = "";
    //  topIC thing/products/产品id/devices/设备sn/command_resp
    /**
     * @var string
     */
    public $pub_topic = '';
    /**
     * @var string
     */
    public $sub_topic = '';

    public $message = NULL;

    public $dev_user = null;


    public function __construct(){
        $this->message = new OnenetMessage();
        $this->dev_user = new DeviceUser();
        $this->clientId = $this->dev_user->getSn();
        $this->productId = $this->dev_user->getProductId();
        $this->devRegCode = $this->dev_user->getRegCode();
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
        return $this->devRegCode;
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
     *
     * [getSubTopic description]
     * @return [type] [description]
     */
    public function getSubTopic() {
        $this->sub_topic = "thing/products/" . $this->productId . "/devices/" . $this->clientId . "/command_resp";
        return $this->sub_topic;
    }

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
     * 设备注册消息
     * [devRegisterMessage description]
     * @return [type] [description]
     */
    public function devRegisterMessage(){
        $this->message->setVersion(1); //版本 1
        $this->message->setMsgId(1); //消息id 1
        $options = [
            'options_type' => OnenetConfig::OPTIONS_TYPE_REQUEST, //请求 常量
            'options_dev' => OnenetConfig::OPTIONS_DEV_GATE, //设备 常量
            'options_target' => OnenetConfig::OPTIONS_TARGET_SYSTEM, //系统 常量
            'options_act' => OnenetConfig::OPTIONS_ACT_REGISTER, //注册 常量
        ];
        $this->message->setOptions($options);
        $func_list = [
            [
                'func_id' => [
                    'data_type' => OnenetConfig::FUNCTION_DATA_STRING, //数据类型 常量
                    'func_type' => OnenetConfig::FUNCTION_TYPE_CUSTOM, //功能类型 常量
                    'resouce_id' => 1,
                ],
                'func_value' => $this->getPassWord(), //注册码
            ],
            // [
            //     'func_id' => [
            //         'data_type' => OnenetConfig::FUNCTION_DATA_STRING, //数据类型
            //         'func_type' => OnenetConfig::FUNCTION_TYPE_CUSTOM, //功能类型
            //         'resouce_id' => 2,
            //     ],
            //     'func_value' => 'x509', //
            // ],
            
        ];
        $this->message->setFunctionList($func_list);
        $content = $this->message->encode();
        return $content;
    }

    /**
     * 上报信息写入json文件
     * [writeReportJsonInfo description]
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    public function writeReportJsonInfo($str){
    	$file_name = __DIR__ . '/DevReportJsonInfo.json';
    	file_put_contents($file_name,$str);
    	return 1;
    }

    /**
     * 获取json文件内容
     * [getReportJsonInfo description]
     * @return [type] [description]
     */
    public static function getReportJsonInfo(){
    	$dev_file_name = __DIR__ . '/DevReportJsonInfo.json';
        $json_info = file_get_contents($dev_file_name);
        // var_dump($json_info);
        $arr = json_decode($json_info,true);
        return $arr;
    } 

  
    /**
     * 初始化mqtt客户端
     * [initMqttClient description]
     * @return [type] [description]
     */
    public function initMqttClient(){
        $options = [
            'debug' => 0,
            'reconnect_period' => 0,
            'clean_session' => true,
            'client_id' => $this->getClientId(),
            'username' => $this->getUserName(),
            'password' => $this->getPassWord(),
        ];
        $mqtt = new Client($this->getHostAddr(), $this->getHostPort(), $options);
        /*客户端连接*/
	    $mqtt->onConnect = function ($mqtt) {
		    /*订阅*/
		    $mqtt->subscribe($this->getSubTopic());
		    /*发布*/
		    $reg_data = $this->devRegisterMessage();// '810000000122e8010c6d5a6442353771434d6c6e4a70b6';
		    // var_dump(pack('H*',$reg_data));
		    $mqtt->publish($this->getPublicTopic(), hex2bin($reg_data));
		};
		/*消息响应*/
		$mqtt->onMessage = function ($topic, $content) {
		    // var_dump($topic, $this->getSubTopic(),$this->message->decode(bin2hex($content)));
			if($topic == $this->getSubTopic()){
				$token = $report_addr = $productId = $reportIp = '';
				$reportPort = 0;
				$respData = $this->message->decode(bin2hex($content));
				if(isset($respData['func_list'])){
					foreach ($respData['func_list'] as $key => $value) {
						if(!isset($value['func_id'])){
							continue;
						} 
						if(!isset($value['func_id']['custom_func_id'])){
							continue;
						}
						$custom_func_id = $value['func_id']['custom_func_id'];
						$func_value = $value['func_value'];
						if($custom_func_id == 59393){
							$productId = $func_value;
						}
						if($custom_func_id == 59394){
							$token = $func_value;
						}
						if($custom_func_id == 59395){
							$token = $func_value;
						}
						if($custom_func_id == 59396){
							$report_addr = $func_value; 
						}
					}
				}
				if(false != strpos($report_addr,':')){
					$report_addr_arr = explode(':',$report_addr);
					$reportIp = $report_addr_arr[0];
					$reportPort = intval($report_addr_arr[1]);
				}
				var_dump("设备注册成功.....");
				var_dump("设备接入Token：".$token);
				var_dump("设备接入地址：".$report_addr);
				$cfg_arr = [
								'reportIp'=>$reportIp,
								'reportPort'=>$reportPort,
								'token'=>$token,
								'productId'=>$productId
							];
				$reportJson = json_encode($cfg_arr,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				$this->writeReportJsonInfo($reportJson);
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