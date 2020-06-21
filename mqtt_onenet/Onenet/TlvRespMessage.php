<?php
namespace Onenet;
/**
 * 上报响应消息体
 * @author jing 
 * @date 2020/06/16
 * 【MQTT 通道】： 如：$sys/{product_id}/{dev_key}/thing/tlv/up/rejected
 **备注：失败响应会包括：消息id、错误码、错误消息**
 *0x00000001 0190 706172616d65746572206572726f72  // id=1;err_code=400;err_msg="parameter error" - 消息id：1；错误码：400；错误消息：参数错误
 *| 参数     | 参数名   | 类型    | 长度（Byte） | 说明            |
| -------- | -------- | ------- | ------------ | --------------- |
| id       | 消息id   | integer | 4            | 非0的无符号整数 |
| err_code | 错误码   | integer | 2            | 无符号整数      |
| err_msg  | 错误消息 | string  | *            | UTF-8编码       |
 */
class TlvRespMessage {

    /**
     * @var int
     */
    public $id = 0; //integer
    /**
     * @var int
     */
    public $err_code = 0; //integer
    /**
     * @var string
     */
    public $err_msg = ''; //string
    /**
     * @var string
     */
    public $sys_err_msg = ''; //string;

    /**
     * 数据拆包
     * [decode description]
     * @param  [type] $hex_str [description]
     * @return [type]          [description]
     */
    public function decode($hex_str) {
        $this->sys_err_msg = '';
        try {
            $hex_str_len = strlen($hex_str);
            if ($hex_str_len < 10) {
                throw new Exception("hex string length no en long", 1);
            }
            /*消息id 4byte*/
            $offer = 0;
            $len = 8;
            $id_hex = mb_substr($hex_str, $offer, $len);
            $t1 = unpack("N", hex2bin($id_hex));
            $this->id = isset($t1[1]) ? $t1[1] : -1;
            /*消息id 4byte*/

            /*错误码 2byte*/
            $offer += $len;
            $len = 4;
            $err_code_hex = mb_substr($hex_str, $offer, $len);
            $t1 = unpack("n", hex2bin($err_code_hex));
            $this->err_code = isset($t1[1]) ? $t1[1] : -1;
            /*错误码 2byte*/

            /*错误消息 nbyte*/
            $offer += $len;
            $err_msg_hex = mb_substr($hex_str, $offer);
            $t1 = unpack("a*", hex2bin($err_msg_hex));
            $this->err_msg = isset($t1[1]) ? $t1[1] : "error";
        } catch (Exception $e) {
            $this->sys_err_msg = $e->getMessage();
        }
        /*错误消息 nbyte*/
        return $this->getMessRespInfo();
    }

    /**
     * 获取响应信息
     * [getMessRespInfo description]
     * @return [type] [description]
     */
    public function getMessRespInfo() {
        return [
            'id' => $this->id,
            'err_code' => $this->err_code,
            'err_msg' => $this->err_msg,
            'sys_err_msg' => $this->sys_err_msg,
        ];
    }
}