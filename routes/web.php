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

Auth::routes();

// todo: name with rest
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/authorization-request', 'Auth\QuickbooksController@makeAuthorizationRequest')->name('qb-authorization-request');
Route::get('/handle-authorization-code', 'Auth\QuickbooksController@handleAuthorizationCode')->name('qb-handle-authorization-code');
Route::get('/handle-access-token', 'Auth\QuickbooksController@handleAccessToken')->name('qb-handle-access-token');
