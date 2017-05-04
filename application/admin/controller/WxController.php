<?php
/*
 * @thinkphp5.0  后台auth认证   php5.3以上
 * @Created on 2016/07/25
 * @Author  Kevin   858785716@qq.com
 * @如果需要公共控制器，就不要继承AdminAuth，直接继承Controller
 */
namespace app\admin\controller;
use app\admin\model\Administrator as AdministratorModel;
use think\Controller;
use think\Model;
use wechat\TPWechat;

//权限认证
class WxController extends Controller {

    /**
     * [wx 微信]
     * @return [type] [description]
     */
    public function wx(){
//        if (isset($_GET["echostr"])) {
//            $echoStr = $_GET["echostr"];
//            echo $echoStr;
//            exit;
//        }
        $options = [
            'appid' => 'wxbe21837cd367991d',
            'token' => 'zhiguo2017415171421',
            'encodingaeskey' => 'dHQRrqW7VaKrnNSQkDyenau49RoPf7EqOtvPzt7S3ci',
            'appsecret' => '6fd9659e71af5ae399ab5fda702bf502',
            'debug' => false
        ];

        $wechat = new TPWechat($options);
        echo $wechat->valid();
    }
}
