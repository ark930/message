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

Route::post('login', 'UserController@login');
Route::post('register', 'UserController@register');

Route::get('usr/{user_id}/follow', 'UserController@getFollow');
Route::post('usr/{user_id}/follow/{f_user_id}', 'UserController@follow');
Route::post('usr/{user_id}/unfollow/{f_user_id}', 'UserController@unfollow');

Route::get('user/{user_id}/group', 'GroupController@show');
Route::post('user/{user_id}/group/create', 'GroupController@createGroup');
Route::post('user/{user_id}/group/{group_id}/join', 'GroupController@join');
Route::post('user/{user_id}/group/{group_id}/quit', 'GroupController@quit');
Route::post('user/{user_id}/group/{group_id}/dismiss', 'GroupController@dismiss');
Route::post('user/{user_id}/group/{group_id}/invite', 'GroupController@invite');