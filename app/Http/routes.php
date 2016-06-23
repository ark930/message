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

Route::group(['prefix' => 'api/v1'], function() {
    Route::group(['middleware' => 'throttle:10000'], function() {
        Route::post('register', 'UserController@register');
        Route::post('login', 'UserController@login');
        Route::post('verifycode', 'UserController@loginVerifyCode');
    });

    Route::group([
        'middleware' => [
            'auth:' . config('message.guard'),
        ]
    ], function () {
        Route::get('user/follow', 'UserController@getFollow');
        Route::post('user/follow/{f_user_id}', 'UserController@follow');
        Route::post('user/unfollow/{f_user_id}', 'UserController@unfollow');

        Route::get('contact', 'ContactController@getContacts');
        Route::get('contact/{f_user_id}', 'ContactController@getContact');
        Route::post('contact/{f_user_id}', 'ContactController@editContact');
        Route::post('contact/{f_user_id}/block', 'ContactController@block');
        Route::post('contact/{f_user_id}/unblock', 'ContactController@unblock');
        Route::post('contact/{f_user_id}/follow', 'ContactController@follow');
        Route::post('contact/{f_user_id}/unfollow', 'ContactController@unfollow');
        Route::post('contact/{f_user_id}/star', 'ContactController@star');
        Route::post('contact/{f_user_id}/unstar', 'ContactController@unstar');

        Route::get('user/group', 'GroupController@show');
        Route::post('user/group/create', 'GroupController@createGroup');
        Route::post('user/group/{group_id}/join', 'GroupController@join');
        Route::post('user/group/{group_id}/quit', 'GroupController@quit');
        Route::post('user/group/{group_id}/dismiss', 'GroupController@dismiss');
        Route::post('user/group/{group_id}/invite', 'GroupController@invite');

        Route::get('user/find', 'UserController@findUsers');
        Route::get('user/profile', 'UserController@getUserProfile');
        Route::post('user/profile', 'UserController@editUserProfile');
        Route::post('user/profile/avatar', 'UserController@editUserAvatar');
        Route::get('user/profile/avatar', 'UserController@getUserAvatar');
        Route::get('user/profile/avatar/{avatar_name}', 'UserController@getAvatarByName');
//        Route::get('user/preference', 'UserController@editPreference');
        Route::post('user/preference', 'UserController@editPreference');
        Route::get('user/device', 'UserController@activeDeviceList');
        Route::post('user/device/{device_id}/logout', 'UserController@logoutDevice');

        Route::post('message', 'MessageController@sendMessage');
        Route::get('message', 'MessageController@getMessage');
    });
});

Route::group([], function() {
    Route::get('/', function() {
        return redirect('login');
    });

    Route::get('login', 'Page\UserController@login');
    Route::get('im', 'Page\IMController@main');

    Route::get('dhc', 'TestController@dhc');
});
