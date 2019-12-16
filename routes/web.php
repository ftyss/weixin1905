<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/text/ceshi','Text\\TextController@ceshi');
Route::get('/text/redis','Text\\TextController@redis1');
Route::get('/user/adduser','User\\LoginController@addUser');
Route::get('/text/xml','Text\\TextController@xmlText');


Route::get('/wx/jk','Weixin\\WxController@weixin'); 
Route::get('/wx/test','Weixin\\WxController@test');               //微信接入
Route::post('/wx/jk','Weixin\\WxController@receiv');
Route::get('/wx/media','Weixin\\WxController@getMedia');            //获取素材

Route::get('text/baidu','Text\\TextController@baidu');          
