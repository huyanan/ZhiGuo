<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use app\admin\model\Users;
use app\admin\model\Companys;
use app\admin\model\Stores;

class IndexController extends Controller
{
    public function index()
    {
    	if ( !is_login() ) {
            $url = config('site_url') . 'weixinauth';
            header("Location: " . $url);
        } else {
            $companys = Companys::where('id', '<>', '-1')->column('name', 'id');
            $user = Users::get(['weixin_openid' => session('w_uid')]);
            // $stores = Stores::where('id', '<>', '-1')->column('name', 'id', 'company_name');
            $stores = Stores::where('id', '<>', '-1')->select();
            $stores_map = [];
            foreach ($stores as $skey => $store) {
                if (!isset($stores_map[$store->company_name])) {
                    $stores_map[$store->company_name] = [];
                }
                $stores_map[$store->company_name][] = $store;
            }

            $wechat = wechat();
            $jsSign = $wechat->getJsSign(get_url());
    	    return view('index', [
                'user' => $user,
                'companys' => $companys,
                'stores' => $stores,
                'stores_map' => json_encode($stores_map),
                'jsSign' => json_encode($jsSign)
            ]);
        }
    }

    /**
	 * 微信授权
	 */
	public function weixin_auth()
    {

        if( !is_login() )
//        if(1)
        {
        	$wechat = wechat();
            // 转向到微信登入页面
            // 转向链接结构: https://open.weixin.qq.com/connect/oauth2/authorize?appid=APPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect
            $redir = config('site_url').'weixincallback';

            $url = $wechat->getOauthRedirect($redir);
            // $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='
            //     .u(c('weixin_akey')).'&redirect_uri='
            //     .u($redir).'&response_type=code&scope='
            //     .u('snsapi_userinfo').'&state=STATE#wechat_redirect';

            header("Location: " . $url );
        }
        else
        {

//            echo '已经登入了';
            header("Location: ".config('client_url')."?token=".session_id('w_uid') );
        }
    }

    public function weixin_callback ($code) {

    	$wechat = wechat();
    	$res = $wechat->getOauthAccessToken();
    	if (!$res) {
    		return '获取access_token失败';
    	}
    	$user_info = $wechat->getOauthUserinfo($res['access_token'], $res['openid']);
    	if (!$user_info) {
    		return '获取用户信息失败';
    	}
    	if (!$user_info['openid']) {
    		return '数据返回错误请重新授权';
    	}
    	// 保存用户信息到数据库
    	$user = Users::get(['weixin_openid' => $user_info['openid']]);
    	if (!$user) {
    		$user = new Users();
    		$user->weixin_openid = $user_info['openid'];
    	}
    	$user->save();
        session('w_uid', $user->weixin_openid);
    	header("Location: ".config('client_url'));

    }

    public function home()
    {
    	return view();
    }
}
