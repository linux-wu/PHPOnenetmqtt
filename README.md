# PHPOnenetmqtt

PHP VERSION Cmcc OneNet Mqtt Message Tool
Instructions for use
1.**cd mqtt_onenet\Onenet directory**;

2.**edit the file name: DeviceInfo.json** . write the cmcc onenet device info :
example:
  {
    "productId": "123423",
    "regCode": "wuuufjvjfus",
    "sn": "sn00001"
}

3. **/usr/local/php/bin/php demo.php** . run for device register.

4.you can see the report info after device register . DevReportJsonInfo.json include the token and report addr.

5.**/usr/local/php/binphp  demo2.php** . run for device report info;
