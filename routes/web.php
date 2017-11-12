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


Route::get('/connect', 'ConnectController@index')->name('connect');

Route::get('/authorization-request', 'Auth\QuickbooksController@makeAuthorizationRequest')->name('qb-authorization-request');
Route::get('/handle-authorization-code', 'Auth\QuickbooksController@handleAuthorizationCode')->name('qb-handle-authorization-code');
Route::get('/revoke-access', 'Auth\QuickbooksController@revokeAccess')->name('qb-revoke-access');

Route::get('/company', 'CompanyController@index')->name('company');
Route::get('/customers', 'CustomersController@index')->name('customers');
