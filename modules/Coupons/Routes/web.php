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

Route::group([
    'middleware' => ['web','impersonate'],
    'namespace' => 'Modules\Coupons\Http\Controllers'
], function () {
    Route::prefix('coupons')->group(function() {

        

         // Coupons
         Route::name('coupons.')->group(function() {
            Route::get('/index', 'CouponsController@index')->name('index');
            Route::get('/{coupon}/edit', 'CouponsController@edit')->name('edit');
            Route::get('/create', 'CouponsController@create')->name('create');
            Route::post('', 'CouponsController@store')->name('store');
            Route::put('/{coupon}', 'CouponsController@update')->name('update');
            Route::get('/del/{coupon}', 'CouponsController@destroy')->name('delete');
            Route::get('/use/{coupon}', 'CouponsController@use')->name('use');
        });

        


    });
});


Route::group([
    'namespace' => 'Modules\Coupons\Http\Controllers'
], function () {
    Route::post('coupons/apply', 'CouponsController@apply')->name('coupons.apply');
});



