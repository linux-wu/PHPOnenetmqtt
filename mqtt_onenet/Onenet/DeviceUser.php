<?php
namespace Onenet;
/**
 * 设备信息
 * @author 
 * @date 2020/06/15
 */
class DeviceUser{


      /**
     * 平台分配的产品ID
     * @var string
     */
    public $productId = "";


    /**
     * 注册码
     * [$regCode description]
     * @var string
     */
    public $regCode = '';

    /**
     *
     * 设备SN
     * @var string
     */
    public $sn = "";

    /**
     * 接入机token
     * [$reportToken description]
     * @var string
     */
    public $reportToken = '';



    public function __construct(){
        $dev_file_name = __DIR__ . '/DeviceInfo.json';
        $devUserInfoJson = file_get_contents($dev_file_name);
        // var_dump($devUserInfoJson);
        $deviceUser = json_decode($devUserInfoJson,true);
        // var_dump($deviceUser);
        $this->sn = isset($deviceUser['sn']) ? $deviceUser['sn'] : '';
        $this->regCode = isset($deviceUser['regCode']) ? $deviceUser['regCode'] : '';
        $this->productId = isset($deviceUser['productId']) ? $deviceUser['productId'] : '';
    }


    /**
     * 获取sn
     * [getSn description]
     * @return [type] [description]
     */
    public function getSn(){
        return $this->sn;
    }

    /**
     * 获取产品id
     * [getProductId description]
     * @return [type] [description]
     */
    public function getProductId(){
        return $this->productId;
    }

    /**
     * 获取注册码
     * [getRregCode description]
     * @return [type] [description]
     */
    public function getRegCode(){
        return $this->regCode;
    }

    /**
     * 设置接入机器token
     * [setReportToken description]
     * @param [type] $token [description]
     */
    public function setReportToken($token){
        $this->reportToken = $token;
    }

    /**
     * 获取接入机器token
     * [getReportToken description]
     * @return [type] [description]
     */
    public function getReportToken(){
        return $this->reportToken;
    }
}