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
    return view('home');
})->middleware('auth');
Auth::routes();

// auto upload/store file to temporary storage path
Route::post('/video/upload', 'VideoController@upload');

// store form information and move file to permanent storage path
Route::post('/me/videos', 'VideoController@getUploadLink');
Route::post('/store', 'VideoController@store');