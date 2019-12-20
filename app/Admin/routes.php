<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->resource('users', WxUserController::class);        //后台用户管理
    $router->resource('wxmsg', WxMsgController::class);     //后台留言管理
    $router->resource('wxgoods', WxGoodsController::class);   //后台商品管理

    $router->get('/wxsendmsg', 'WxSendMsgController@sendMsg');    //微信群发消息
});
