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

Route::prefix('whatsi')->group(function() {
    Route::get('/', 'WhatsiController@index');
});


Route::group([
    'namespace' => 'Modules\Whatsi\Http\Controllers'
], function () {
    Route::prefix('whatsi')->group(function() {
        Route::group([
            'middleware' =>[ 'web','impersonate'],
        ], function () {
            Route::get('/set_api', 'Main@setAPI')->name('whatsi.api');
            Route::post('/store_api', 'Main@store_api_key')->name('whatsi.store_api');
            Route::get('/set_campaings', 'Main@set_campaings')->name('whatsi.campaings');
        });
        
    });
});

