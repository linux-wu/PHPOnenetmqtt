<?php
namespace Onenet;
/**
 * Onenet mqtt通用协议消息体
 * @author jing
 * @date 2020/06/14
 */

class OnenetMessage {
    /**
     * @var mixed
     */
    public $version;
    /**
     * @var mixed
     */
    public $msg_id;
    /**
     * @var mixed
     */
    public $options;
    /**
     * @var mixed
     */
    public $code = '0';
    /**
     * @var mixed
     */
    public $func_list;
    /**
     * @var mixed
     */
    public $crc;
    /**
     * @var mixed
     */
    public $buf;

    /**
     *
     * [setVersion description]
     * @param [type] $version [description]
     */
    public function setVersion($version) {
        $this->version = intval($version);
    }

    /**
     *
     * [setMsgId description]
     * @param [type] $version [description]
     */
    public function setMsgId($msg_id) {
        $this->msg_id = intval($msg_id);
    }

    /**
     *
     * [setOptions description]
     * @param [type] $version [description]
     */
    public function setOptions($options) {
        $this->options = $options;
    }

    /**
     *
     * [setCode description]
     * @param [type] $version [description]
     */
    public function setCode($code) {
        $this->code = intval($code);
    }

    /**
     * [setFunctionList description]
     * @param [type] $func_list [description]
     */
    public function setFunctionList($func_list) {
        $this->func_list = $func_list;
    }

    /**
     * CRC ccitt-false 模式
     * [crc162 description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function crc162($str) {
        $data = pack('H*', $str);
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $x = (($crc >> 8) ^ ord($data[$i])) & 0xFF;
            $x ^= $x >> 4;
            $crc = (($crc << 8) ^ ($x << 12) ^ ($x << 5) ^ $x) & 0xFFFF;
        }
        return $crc;
    }


    /**
     * 功能点数据处理
     * [funcDataEncode description]
     * @param  [type] $funcObj       [description]
     * @param  [type] &$func_tmp_buf [description]
     * @return [type]                [description]
     */
    public function funcDataEncode($funcObj,&$func_tmp_buf){
        /*func_id 2byte*/
            $func_id = isset($funcObj['func_id']) ? $funcObj['func_id'] : [];
            $custom_func_id = isset($funcObj['custom_func_id']) ? $funcObj['custom_func_id'] : 0;
            if (!empty($custom_func_id)) {
                /*自定义功能点*/
                $func_id['data_type'] = $custom_func_id >> 13 & 7;
                $func_id['func_type'] = $custom_func_id >> 11 & 3;
                $func_id['resouce_id'] = $custom_func_id & 0x3ff;
                $func_tmp_buf[] = bin2hex(pack('n', $custom_func_id));
            } else {
                /*固定功能点*/
                $func_id_bit = $func_id['data_type'] << 13;
                $func_id_bit = $func_id_bit | ($func_id['func_type'] << 11);
                $func_id_bit = $func_id_bit | $func_id['resouce_id'];
                $func_tmp_buf[] = bin2hex(pack('n', $func_id_bit));
            }
            /*func_id 2byte*/

            /*func_value nbyte*/
            $func_value = $funcObj['func_value'];
            /*长度处理动态长度位的字节*/
            /*00b
            01b
            10b
            11b
             */
            $len_has = 0;
            switch ($func_id['data_type']) {
            case 5://buffer
            case 7://string
                $len_has = 1;
                break;
            default:
                break;
            }
            if ($len_has) {
                $value_len = strlen($func_value);
                $def_len_bit = '00';
                $len_bit_other = '00000000';
                $len_pos = 1;

                if ($value_len > 0 && $value_len < 64) {
                    $len_pos = 1;
                    $len_bit_other = (0 << 8) | $value_len;
                    // var_dump($len_bit_other);exit;
                    $func_tmp_buf[] = bin2hex(pack('C', $len_bit_other));
                }
                if ($value_len >= 64 && $value_len < 16384) {
                    $len_bit_other = (1 << 14) | $value_len;
                    // var_dump($len_bit_other);//1000000001000000
                    $func_tmp_buf[] = bin2hex(pack('n', $len_bit_other));
                }
                if ($value_len >= 16384 && $value_len < 4194304) {
                    $len_pos = 2;
                    $len_bit_other = (2 << 22) | $value_len;
                    $c1 = $len_bit_other >> 8;
                    $c2 = $len_bit_other & 255;
                    // var_dump($c1,$c2);
                    $func_tmp_buf[] = bin2hex(pack('nC', $c1, $c2));
                }
                if ($value_len >= 4194304) {
                    $len_bit_other = (3 << 30) | $value_len;
                    $func_tmp_buf[] = bin2hex(pack('N', $len_bit_other));
                }
            }

            ////var_dump($p_format);exit;
            ////var_dump($func_value);
            //         001b 1   不存在
            // 010b    1（无符号整数）    不存在
            // 011b    4（有符号整数）    不存在
            // 100b    4（有符号浮点数）   不存在
            // 101b    <=0xFFFFFFFF    存在
            // 110b    4（无符号整数）    不存在
            // 111b    <=0xFFFFFFFF    存在
            //判断数据类型 进行对应的格式pack打包
            switch ($func_id['data_type']) {
            case 0: //保留
                # code...
                break;
            case 1: //boolean
                $func_tmp_buf[] = bin2hex(pack("c", $func_value));
                break;
            case 2: //enum
                $func_tmp_buf[] = bin2hex(pack("N", $func_value));
                break;
            case 3: //integer
                $func_tmp_buf[] = bin2hex(pack("N", $func_value));
                // var_dump($func_tmp_buf);exit;
                break;
            case 4: //float
                $func_tmp_buf[] = bin2hex(pack("f", $func_value));
                // var_dump($func_tmp_buf);exit;
                break;
            case 5: //buffer
                $func_tmp_buf[] = bin2hex(pack("H*", $func_value));
                break;
            case 6: //exception
                $func_tmp_buf[] = bin2hex(pack("N", $func_value));
                break;
            case 7: //string
                // var_dump($func_value);
                $func_tmp_buf[] = bin2hex(pack("a*", $func_value));
                break;
            default:
                $func_tmp_buf[] = bin2hex(pack("a*", $func_value));
                break;
            }

            /*func_value nbyte*/
            // exit;
    }

    /**
     * 数据封包
     * [encode description]
     * @return [type] [description]
     */
    public function encode() {
        $this->buf = [];
        $version_bit = !empty($this->msg_id) ? 1 << 7 : 0 << 7;
        $version_bit = $version_bit | $this->version;
        $this->buf[] = bin2hex(pack('C', $version_bit));
        // var_dump($this->buf);
        /*=======1byte 版本号 =======*/

        /*=======4byte 消息id =======*/
        $this->buf[] = bin2hex(pack('N', $this->msg_id)); //32bit
        ////var_dump($this->buf);
        /*=======4byte 消息id =======*/

        /*=======1byte 操作码 =======*/
        $options = 0;
        /*1.操作类型【1bit】 -- 请求 0 /响应 1
        2.设备类型【1bit】 -- 设备 0
        3.操作对象【1bit】 -- 系统 1
        4.操作名【5bit】 -- 注册 00010b
         */
        $option_bit = $this->options['options_type'] << 7;
        $option_bit = $option_bit | ($this->options['options_dev'] << 6);
        $option_bit = $option_bit | ($this->options['options_target'] << 5);
        $option_bit = $option_bit | $this->options['options_act'];
        $this->buf[] = bin2hex(pack('C', $option_bit));
        ////var_dump($this->buf);
        /*=======1byte 操作码 =======*/

        /*=======1byte 响应码 当operations中操作类型为响应时 存在响应码，成功响应时为0 =======*/
        if ($this->options['options_type'] == '1') {
            $this->buf[] = bin2hex(pack('C', 0));
        }
        ////var_dump($this->buf);
        /*=======1byte 响应码 =======*/

        /*=======nbyte function =======*/
        // 功能点组成：
        //   1.数据类型【3bit】 111 字符串
        //   2.功能类型【2bit】 - 业务自定义 01
        //   3.资源ID【11bit】

        //       功能点数据组成：
        // 1. 功能点长度【可变】
        // 2. 功能点值
        $func_tmp_buf = [];
        foreach ($this->func_list as $key => $funcObj) {
            $this->funcDataEncode($funcObj,$func_tmp_buf);
        }
        if (!empty($func_tmp_buf)) {
            $this->buf[] = implode('', $func_tmp_buf);
        }
        ////var_dump('####',$this->buf);
        /*=======nbyte function =======*/

        $data_tmp = implode('', $this->buf);
        /*=======CRC 2byte =======*/
        $dec_crc = $this->crc162($data_tmp);
        $this->buf[] = bin2hex(pack('n', $dec_crc));
        ////var_dump($this->buf);
        /*=======CRC 2byte =======*/
        return implode('', $this->buf);
    }

    /**
     * 数据拆包
     * [decode description]
     * @param  [type] $hex_str [description]
     * @return [type]          [description]
     */
    public function decode($hex_str) {
        /*去掉校验位*/
        $hex_str = mb_substr($hex_str, 0, -4);
        $hex_str_len = strlen($hex_str);
        /*810000000122e8010c3135437a66385270304c7246e8020478353039d0c2*/
        $offer = 0; //偏移量
        $len = 2;
        if ($offer >= $hex_str_len) {
            return [];
        }
        /*1版本号 1byte*/
        $version = mb_substr($hex_str, $offer, $len); //1个字节
        $t = unpack('C', hex2bin($version));
        if (!isset($t[1])) {
            throw new Exception("Error version Request", 1);
        }
        $this->version = $t[1] & 0x7f; //掩码 127 1000000进行位运算 取到第一位的数值
        ////var_dump($this->version);
        /*1版本号 1byte*/
        /*2消息id 4byte*/
        $offer += $len;
        $len = 8;
        if ($offer >= $hex_str_len) {
            return [];
        }
        $msg_id = mb_substr($hex_str, $offer, $len); //4个字节
        $t = unpack('N', hex2bin($msg_id));
        if (!isset($t[1])) {
            throw new Exception("Error msg_id Request", 1);
        }
        $this->msg_id = $t[1];
        ////var_dump($this->msg_id);

        /*2消息id 4byte*/
        /*=======1byte 操作码 =======*/
        $offer += $len;
        $len = 2;
        if ($offer >= $hex_str_len) {
            return [];
        }
        $options = mb_substr($hex_str, $offer, $len); //1个字节
        ////var_dump($options);
        $t = unpack('C', hex2bin($options));
        if (!isset($t[1])) {
            throw new Exception("Error options Request", 1);
        }
        ////var_dump($t[1]);
        $this->options['options_type'] = $t[1] >> 7 & 1;
        $this->options['options_dev'] = $t[1] >> 6 & 1;
        $this->options['options_target'] = $t[1] >> 5 & 1;
        $this->options['options_act'] = $t[1] & 0x1f;
        ////var_dump($this->options);
        /*=======1byte 操作码 =======*/

        /*=======1byte 响应码 当operations中操作类型为响应时 存在响应码，成功响应时为0 =======*/
        if ($this->options['options_type'] == 1) {
            $offer += $len;
            $len = 2;
            $resp_code = mb_substr($hex_str, $offer, $len); //1个字节
            ////var_dump($resp_code);
            $t = unpack('C', hex2bin($resp_code));
            if (!isset($t[1])) {
                throw new Exception("Error resp_code Request", 1);
            }
            ////var_dump($t[1]);
            $this->code = $t[1];
            // 0x00 成功
            // 0x01:// 产品不存在
            // 0x02:// 产品权限错误
            // 0x03:// 产品未定义功能模型
            // 0x04:// 设备标识长度或定义规则不符合规范
            // 0x05:// 设备无接入权限 - 开启白名单
            // 0x06:// 设备IMEI号已被占用
            // 0x80:// 系统内部错误
            // 0x81:// 请求消息格式或参数错误
            switch ($this->code) {
            case 0:
                //成功
                break;
            case 1: // 产品不存在
                throw new Exception("产品不存在", 1);
                break;
            case 2: // 产品权限错误
                throw new Exception("产品权限错误", 1);
                break;
            case 3: // 产品未定义功能模型
                throw new Exception("产品未定义功能模型", 1);
                break;
            case 4: // 设备标识长度或定义规则不符合规范
                throw new Exception("设备标识长度或定义规则不符合规范", 1);
                break;
            case 5: // 设备无接入权限 - 开启白名单:
                throw new Exception("设备无接入权限 - 开启白名单", 1);
                break;
            case 6: // 设备IMEI号已被占用:
                throw new Exception("设备IMEI号已被占用", 1);
                break;
            case 128: // 系统内部错误:
                throw new Exception("系统内部错误", 1);
                break;
            case 129: // 请求消息格式或参数错误
                throw new Exception("请求消息格式或参数错误", 1);
                break;
            default:
                # code...
                break;
            }
        }
        // ////var_dump($this->buf);
        /*=======1byte 响应码 =======*/

        /*=======nbyte function =======*/
        // 功能点组成：
        //   1.数据类型【3bit】 111 字符串
        //   2.功能类型【2bit】 - 业务自定义 01
        //   3.资源ID【11bit】

        //       功能点数据组成：
        // 1. 功能点长度【可变】
        // 2. 功能点值
        /*剩余总长度*/
        $offer += $len;
        if ($offer >= $hex_str_len) {
            return [];
        }
        $this->func_list = [];
        while ($offer < $hex_str_len) {
            $this->func_list[] = $this->getFunctionValue($hex_str, $offer, $len);
            $offer += $len;
        }
        // $this->func_list[] = $this->getFunctionValue($hex_str,$offer,$len);
        ////var_dump($this->func_list);
        // ////var_dump('####',$this->buf);
        /*=======nbyte function =======*/
        return $this->retRespData();
    }

    /**
     * 返回数据格式
     */
    public function retRespData() {
        return [
            'version' => $this->version,
            'msg_id' => $this->msg_id,
            'options' => $this->options,
            'code' => $this->code,
            'func_list' => $this->func_list,
        ];
    }

    /**
     * 获取funclist
     * [getFunctionValue description]
     * @param  [type] $hex_str [description]
     * @param  [type] &$offer  [description]
     * @param  [type] &$len    [description]
     * @return [type]          [description]
     */
    public function getFunctionValue($hex_str, &$offer, &$len) {
        /*func_id*/
        $len = 4;
        $first_func_id = mb_substr($hex_str, $offer, $len);
        //var_dump([$first_func_id, $hex_str]);
        $t1 = unpack('n', hex2bin($first_func_id));
        if (!isset($t1[1])) {
            throw new Exception("Error first_func_id Request", 1);
        }
        $func_id = [];
        $func_id['data_type'] = $t1[1] >> 13 & 7;
        $func_id['func_type'] = $t1[1] >> 11 & 3;
        $func_id['resouce_id'] = $t1[1] & 0x3ff;
        $func_id['custom_func_id'] = $t1[1];
        /*func_id*/
        /*func_value*/
        $len_has = 0;
        $func_value_len = 0;
        //var_dump(['data_type', $func_id['data_type']]);
        switch ($func_id['data_type']) {
        case 0: //保留
            # code...
            break;
        case 1: //boolean
            $func_value_len = 1;
            break;
        case 2: //enum
            $func_value_len = 1;
            break;
        case 3: //integer
            $func_value_len = 4;
            break;
        case 4: //float
            $func_value_len = 4;
            break;
        case 5: //buffer
            $len_has = 1;
            break;
        case 6: //exception
            $func_value_len = 4;
            break;
        case 7: //string
            // ////var_dump($func_value_hex);
            $len_has = 1;
            break;
        default:
            break;
        }
        /*是否有长度字节*/
        if ($len_has) {
            $offer += $len;
            $len = 2; //先获取一个字节
            $value_len_hex = mb_substr($hex_str, $offer, $len);
            if (empty($value_len_hex)) {
                ////var_dump($offer,$len,$value_len_hex);
            }
            $t1 = unpack('C', hex2bin($value_len_hex));
            if (!isset($t1[1])) {
                throw new Exception("Error first_func_id Request", 1);
            }
            $len_bit_type = $t1[1] >> 6 & 3;
            // ////var_dump($len_bit_type);
            //  00b 1byte
            // 01b 2byte
            // 10b 3byte
            // 11b 4byte
            // 16384  4194304  4194304
            switch ($len_bit_type) {
            case 0: //长度
                $func_value_len = $t1[1] & 63;
                break;
            case 1: //2byte
                $offer += $len;
                $len = 2;
                $value_len_hex .= mb_substr($hex_str, $offer, $len);
                // ////var_dump($value_len_hex);
                $t1 = unpack('n', hex2bin($value_len_hex));
                if (!isset($t1[1])) {
                    throw new Exception("Error first_func_id Request", 1);
                }
                //16bit
                //64 123
                //8bit << 16bit | 后面8bit与 & 取后面14位
                $func_value_len = $t1[1] & 16383;
                break;
            case 2: //3byte
                $offer += $len;
                $len = 4;
                $value_len_hex .= mb_substr($hex_str, $offer, $len);
                $t1 = unpack('nC', hex2bin($value_len_hex));
                if (!isset($t1[1])) {
                    throw new Exception("Error first_func_id Request", 1);
                }
                //16bit
                //64 123 1111111111111111111111

                $func_value_len = $t1[1] & 4194303; //取22bit
                break;
            case 3: //4byte
                $offer += $len;
                $len = 6;
                $value_len_hex .= mb_substr($hex_str, $offer, $len);
                $t1 = unpack('N', hex2bin($value_len_hex));
                if (!isset($t1[1])) {
                    throw new Exception("Error first_func_id Request", 1);
                }
                $func_value_len = $t1[1] & 1073741823; //32 取30bit
                break;
            default:
                break;
            }
        }

        // ////var_dump($func_value_len);
        $offer += $len;
        $len = $func_value_len * 2; //123 * 8 / 4 个字节
        // ////var_dump($len,$func_value_len);exit;
        $func_value_hex = mb_substr($hex_str, $offer, $len);
        /*"000b:保留
        001b:boolean
        010b:enum
        011b:integer
        100b:float
        101b:buffer
        110b:exception
        111b:string*/
        $func_value = '';
        // ////var_dump($func_id['data_type']);
        // var_dump($func_id['data_type']);
        switch ($func_id['data_type']) {
        case 0: //保留
            break;
        case 1: //boolean
            $t1 = unpack('c', hex2bin($func_value_hex));
            if (!isset($t1[1])) {
                throw new Exception("Error first_func_id Request", 1);
            }
            $func_value = $t1[1];
            break;
        case 2: //enum
            $t1 = unpack('N', hex2bin($func_value_hex));
            if (!isset($t1[1])) {
                throw new Exception("Error first_func_id Request", 1);
            }
            $func_value = $t1[1];
            break;
        case 3: //integer
            $t1 = unpack('N', hex2bin($func_value_hex));
            if (!isset($t1[1])) {
                throw new Exception("Error first_func_id Request", 1);
            }
            $func_value = $t1[1];
            break;
        case 4: //float
            $t1 = unpack('f', hex2bin($func_value_hex));
            if (!isset($t1[1])) {
                throw new Exception("Error first_func_id Request", 1);
            }
            $func_value = $t1[1];
            break;
        case 5: //buffer
            // ////var_dump($func_value_hex);
            $t1 = unpack('H*', hex2bin($func_value_hex));
            if (!isset($t1[1])) {
                throw new Exception("Error first_func_id Request", 1);
            }
            $func_value = $t1[1];
            break;
        case 6: //exception
            $t1 = unpack('N', hex2bin($func_value_hex));
            if (!isset($t1[1])) {
                throw new Exception("Error first_func_id Request", 1);
            }
            $func_value = $t1[1];
            break;
        case 7: //string
            // ////var_dump($func_value_hex);
            $t1 = unpack('a*', hex2bin($func_value_hex));
            if (!isset($t1[1])) {
                throw new Exception("Error first_func_id Request", 1);
            }
            $func_value = $t1[1];
            break;
        default:
            $t1 = unpack('a*', hex2bin($func_value_hex));
            if (!isset($t1[1])) {
                throw new Exception("Error first_func_id Request", 1);
            }
            $func_value = $t1[1];
            break;
        }
        /*func_value*/
        return ['func_id' => $func_id, 'func_value' => $func_value];
    }

    /*
     * 判断是否校验成功
     * [crcDataCheck description]
     * @param  [type] $hex_str [description]
     * @return [type]          [description]
     */
    /**
     * @param $hex_str
     * @return mixed
     */
    public function crcDataCheck($hex_str) {
        $data_str = mb_substr($hex_str, 0, -4);
        // ////var_dump($data_str);
        $pt_hex_str = $data_str . bin2hex(pack('n', $this->crc162($data_str)));
        return $pt_hex_str == $hex_str ? true : false;
    }

}