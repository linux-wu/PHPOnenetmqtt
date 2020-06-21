<?php
namespace Onenet;
/**
 * 配置枚举
 * @author jing
 * @date 2020/06/16
 */
class OnenetConfig {

    /*=======操作======*/
    const OPTIONS_TYPE_REQUEST = 0; //操作 请求
    const OPTIONS_TYPE_RESPONSE = 1; //操作 响应
    /*=======操作======*/

    /*=======设备类型======*/
    const OPTIONS_DEV_GATE = 0; //直连设备/网关
    const OPTIONS_DEV_GATE_SUB = 1; //网关的子设备
    /*=======设备类型======*/

    /*=======操作对象======*/
    const OPTIONS_TARGET_RESOURCE = 0; //资源
    const OPTIONS_TARGET_SYSTEM = 1; //系统
    /*=======操作对象======*

    /*=======操作名======*/
    const OPTIONS_ACT_READ = 1; //资源读取
    const OPTIONS_ACT_WRITE = 2; //资源写入
    const OPTIONS_ACT_NOTIFY = 3; //资源上报
    const OPTIONS_ACT_RESET = 0; //复位
    const OPTIONS_ACT_RECOVERY = 1; //恢复出厂设置
    const OPTIONS_ACT_REGISTER = 2; //设备注册
    const OPTIONS_ACT_DEREGISTER = 3; //设备注销
    const OPTIONS_ACT_ENABLE = 4; //设备启用
    const OPTIONS_ACT_DISABLE = 5; //设备禁用
    const OPTIONS_ACT_LABEL = 6; //设备标签
    const OPTIONS_ACT_UPGRADE = 7; //设备升级
    const OPTIONS_ACT_ONLINE = 31; //上线（子设备专用）
    const OPTIONS_ACT_OFFLINE = 30; //离线（子设备专用）
    /*=======操作名======*/

//     "00b:保留"000b:保留
    // 001b:boolean
    // 010b:enum
    // 011b:integer
    // 100b:float
    // 101b:buffer
    // 110b:exception
    // 111b:string
    // | 数据标识  | 数据名 | 位表示 | 占用字节数 (Byte) |
    /*说明                     |
    | --------- | ------ | :----: | :---------------: | :------------------------------------------: |
    | Boolean   | 布尔值 |  001b  |         1         |               0-false，1-true                |
    | Enum      | 枚举值 |  010b  |         1         |           无符号整数，范围[0-255]            |
    | Integer   | 整数   |  011b  |         4         |                  有符号整数                  |
    | Float     | 浮点数 |  100b  |         4         |               **单精度**浮点数               |
    | Buffer    | 二进制 |  101b  |   <= 0x3FFFFFFF   |                十六进制字符串                |
    | Exception | 故障型 |  110b  |         4         | 每一位表示一种故障，1-故障发生，0-故障未发生 |
    | String    | 字符串 |  111b  |   <= 0x3FFFFFFF   |       UTF-8编码，可用于上报JSON字符串        |
     */
    /*======数据类型====*/
    const FUNCTION_DATA_BOOLEAN = 1;
    const FUNCTION_DATA_ENUM = 2;
    const FUNCTION_DATA_INTEGER = 3;
    const FUNCTION_DATA_FLOAT = 4;
    const FUNCTION_DATA_BUFFER = 5;
    const FUNCTION_DATA_EXCEPTION = 6;
    const FUNCTION_DATA_STRING = 7;
    /*======数据类型====*/

    /*======功能类型====*/
    const FUNCTION_TYPE_DEF = 0; //保留
    const FUNCTION_TYPE_CUSTOM = 1; //业务自定义
    const FUNCTION_TYPE_ATTR = 2; //属性
    const FUNCTION_TYPE_EVENT = 3; //事件
    /*======功能类型====*/

//     "普通功能点ID：0x00~0x4ff 0-1279
    // 组合功能点ID：0x500~0x5ff 1280-1535
    // 固定功能点ID: 0x700~0x7ff" 1536-2047
    /*======资源id====*/
    /*======资源id====*/
}