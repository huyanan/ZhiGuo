<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
     // 全局变量规则定义
    '__pattern__'         => [
        'name'  => '\w+',
        'id'    => '\d+',
        'year'  => '\d{4}',
        'month' => '\d{2}',
    ],
    // 路由规则定义
    'wx/'                                        => 'admin/wx/wx',
    'home/'                                      => 'index/home',
    'admin/login/'                               => 'admin/index/login',
    'admin/login_action/'                        => 'admin/index/login_action',
    'admin/lost_password/'                       => 'admin/index/lost_password',
    'admin/logout/'                              => 'admin/index/logout',

    'admin/administrator/:id'                    => 'admin/administrator/read',
    'admin/administrator/update/:id'             => 'admin/administrator/update',
    'admin/administrator/delete/:id'             => 'admin/administrator/delete',
    'admin/administrator/delete_image/:id'       => 'admin/administrator/delete_image',
    'admin/administrator/update_expire_time/:id' => 'admin/administrator/update_expire_time',

    'admin/posts/:id'                            => 'admin/posts/read',
    'admin/posts/update/:id'                     => 'admin/posts/update',
    'admin/posts/delete/:id'                     => 'admin/posts/delete',
    'admin/posts/delete_image/:id'               => 'admin/posts/delete_image',

    'admin/companys/:id'                         => 'admin/companys/read',
    'admin/companys/update/:id'                  => 'admin/companys/update',
    'admin/companys/delete/:id'                  => 'admin/companys/delete',
    'admin/companys/delete_image/:id'            => 'admin/companys/delete_image',

    'admin/users/:id'                            => 'admin/users/read',
    'admin/users/update/:id'                     => 'admin/users/update',
    'admin/users/updateById/:id'                 => 'admin/users/updateById',
    'admin/users/delete/:id'                     => 'admin/users/delete',
//    'admin/users/delete/:id'                     => 'admin/posts/delete',
//    'admin/users/delete_image/:id'               => 'admin/posts/delete_image',

    'admin/stores/:id'                           => 'admin/stores/read',
    'admin/stores/update/:id'                    => 'admin/stores/update',
    'admin/stores/delete/:id'                    => 'admin/stores/delete',
    'admin/stores/delete_image/:id'              => 'admin/stores/delete_image',

    'weixinauth'                                 => 'index/index/weixin_auth',
    'weixincallback'                             => 'index/index/weixin_callback',
    'admin/goods/:id'                            => 'admin/goods/read',
    'admin/goods/qrcode/:id'                     => 'admin/goods/generateQrcode',
    'admin/goods/qrcode'                         => 'admin/goods/qrcode',
    'admin/goods/update/:id'                     => 'admin/goods/update',
    'admin/goods/delete/:id'                     => 'admin/goods/delete',
    'admin/goods/delete_image/:id'               => 'admin/goods/delete_image',

    'admin/sale_records/add'                      => 'admin/sale_records/add',
    'admin/sale_records/:id'                            => 'admin/sale_records/read',
    'admin/sale_records/update/:id'                     => 'admin/sale_records/update',
    'admin/sale_records/delete/:id'                     => 'admin/sale_records/delete',
    'admin/sale_records/exchange/:id'            => 'admin/sale_records/exchange'

];