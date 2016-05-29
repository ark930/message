<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('api/register', 'UserController@register');
Route::post('api/login', 'UserController@login');

Route::group(
    [
        'prefix' => 'api',
        'middleware' => 'auth:api',
//        'middleware'=>'throttle:5',
    ],
    function () {
        Route::get('usr/follow', 'UserController@getFollow');
        Route::post('usr/follow/{f_user_id}', 'UserController@follow');
        Route::post('usr/unfollow/{f_user_id}', 'UserController@unfollow');

        Route::get('user/group', 'GroupController@show');
        Route::post('user/group/create', 'GroupController@createGroup');
        Route::post('user/group/{group_id}/join', 'GroupController@join');
        Route::post('user/group/{group_id}/quit', 'GroupController@quit');
        Route::post('user/group/{group_id}/dismiss', 'GroupController@dismiss');
        Route::post('user/group/{group_id}/invite', 'GroupController@invite');
        
        Route::post('message', 'MessageController@sendMessage');
        Route::get('message', 'MessageController@getMessage');
    });

Route::auth();

Route::get('/home', 'HomeController@index');

Route::resource('test','TestController');
