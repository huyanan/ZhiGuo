<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use wechat\TPWechat;


/**
 * This function adds once the CKEditor's config vars
 * @author Samuel Sanchez
 * @access private
 * @param array $data (default: array())
 * @return string
 */
function cke_initialize($data = array()) {

	$return = '';

	if(!defined('CI_CKEDITOR_HELPER_LOADED')) {
		if (!isset($data['path'])) $data['path'] = '/static/editor/ckeditor/';
		define('CI_CKEDITOR_HELPER_LOADED', TRUE);
		$return =  '<script type="text/javascript" src="'.$data['path'] . 'ckeditor.js"></script>';
		$return .=  '<script type="text/javascript" src="/static/editor/ckfinder/ckfinder.js"></script>';
		$return .=	"<script type=\"text/javascript\">CKEDITOR_BASEPATH = '" . $data['path'] . "';</script>";
	}

	return $return;

}

/**
 * This function create JavaScript instances of CKEditor
 * @author Samuel Sanchez
 * @access private
 * @param array $data (default: array())
 * @return string
 */
function cke_create_instance($data = array()) {

    $return = "<script type=\"text/javascript\">
     	var editor = CKEDITOR.replace('" . $data['id'] . "', {";

    		if(!isset($data['config']['width'])) $data['config']['width'] = '600';
			if(!isset($data['config']['height'])) $data['config']['height'] = '600';

    		//Adding config values
    		if(isset($data['config'])) {


	    		foreach($data['config'] as $k=>$v) {

	    			// Support for extra config parameters
	    			if (is_array($v)) {
	    				$return .= $k . " : [";
	    				$return .= config_data($v);
	    				$return .= "]";

	    			}
	    			else {
	    				$return .= $k . " : '" . $v . "'";
	    			}

	    			if(array_key_exists($k,$data['config'])) {
						$return .= ",";
					}
	    		}
    		}

    $return .= '}); CKFinder.setupCKEditor( editor, "__PUBLIC__/editor/ckfinder/" );</script>';

    return $return;

}

/**
 * This function displays an instance of CKEditor inside a view
 * @author Samuel Sanchez
 * @access public
 * @param array $data (default: array())
 * @return string
 */
function display_ckeditor($data = array())
{
	// Initialization
	$return = cke_initialize($data);

    // Creating a Ckeditor instance
    $return .= cke_create_instance($data);


    // Adding styles values
    if(isset($data['styles'])) {

    	$return .= "<script type=\"text/javascript\">CKEDITOR.addStylesSet( 'my_styles_" . $data['id'] . "', [";


	    foreach($data['styles'] as $k=>$v) {

	    	$return .= "{ name : '" . $k . "', element : '" . $v['element'] . "', styles : { ";

	    	if(isset($v['styles'])) {
	    		foreach($v['styles'] as $k2=>$v2) {

	    			$return .= "'" . $k2 . "' : '" . $v2 . "'";

					if($k2 !== end(array_keys($v['styles']))) {
						 $return .= ",";
					}
	    		}
    		}

	    	$return .= '} }';

	    	if($k !== end(array_keys($data['styles']))) {
				$return .= ',';
			}


	    }

	    $return .= ']);';

		$return .= "CKEDITOR.instances['" . $data['id'] . "'].config.stylesCombo_stylesSet = 'my_styles_" . $data['id'] . "';
		</script>";
    }

    return $return;
}

/**
 * config_data function.
 * This function look for extra config data
 *
 * @author ronan
 * @link http://kromack.com/developpement-php/codeigniter/ckeditor-helper-for-codeigniter/comment-page-5/#comment-545
 * @access public
 * @param array $data. (default: array())
 * @return String
 */
function config_data($data = array())
{
	$return = '';
	foreach ($data as $k => $key)
	{
		if (is_array($key)) {
			$return .= "[";
			foreach ($key as $k2 => $string) {
				$return .= "'" . $string . "'";
				if(array_key_exists($k2,$key)) $return .= ",";
			}
			$return .= "]";
		}
		else {
			$return .= "'".$key."'";
		}
		if(array_key_exists($k,$key)) $return .= ",";

	}
	return $return;
}


// 检测是否登录
function is_login() {
	// var_dump(session('w_uid'));
	// exit;
	// return isset(session('w_uid')) &&  intval(session('w_uid')) > 0;
	return session('?w_uid');
}

// 获取wechat
function wechat()
{
    if(!isset($GLOBALS['wechat']))
    {
        // require( AROOT . 'extend' . DS . 'webchat' . DS . 'TPWechat.php');
        $options = array
        (
            'token'=>config('wexin.token'), //填写你设定的key
            'encodingaeskey'=>config('weixin.encodingaeskey'), //填写加密用的EncodingAESKey
            'appid'=>config('weixin.appid'), //填写高级调用功能的app id, 请在微信开发模式后台查询
            'appsecret'=>config('weixin.appsecret') //填写高级调用功能的密钥
        );

        $GLOBALS['wechat'] = new TPWechat($options);
    }

    return $GLOBALS['wechat'];
}

/**
 * 获取当前页面完整URL地址
 */
function get_url() {
    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
    return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
}


/**
 * Simple function to replicate PHP 5 behaviour
 */
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

function unlink_dir($path) {
    $_path = realpath($path);
    if (!file_exists($_path)) return false;
    if (is_dir($_path)) {
        $list = scandir($_path);
        foreach ($list as $v) {
            if ($v == '.' || $v == '..') continue;
            $_paths = $_path.'/'.$v;
            if (is_dir($_paths)) {
                unlink_dir($_paths);
            } elseif (unlink($_paths) === false) {
                return false;
            }
        }
        return true;
    }
    return !is_file($_path) ? false : unlink($_path);
 }