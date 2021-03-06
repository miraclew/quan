<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('hello');
});

Route::resource('users', 'UserController', array('only' => array('show','update','index')));
Route::resource('circles', 'CircleController');
Route::resource('friends', 'FriendController');
Route::resource('follows', 'FollowController');
Route::resource('members', 'MemberController');
Route::resource('posts', 'PostController');
Route::resource('comments', 'CommentController');
Route::resource('likes', 'LikeController');

Route::resource('channels', 'ChannelController');
Route::resource('messages', 'MessageController');

Route::resource('helps', 'HelpController');

Route::controller('debug', 'DebugController');
Route::controller('users', 'UserController');
Route::controller('files', 'FileController');
