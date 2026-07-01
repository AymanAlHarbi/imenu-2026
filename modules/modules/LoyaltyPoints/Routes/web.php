<?php

Route::group([
    'middleware' => ['web', 'auth', 'impersonate'],
    'namespace' => 'Modules\LoyaltyPoints\Http\Controllers',
], function () {

    Route::prefix('loyalty')->group(function () {
        Route::name('loyaltypoints.')->group(function () {
            // Client: view points + redeem
            Route::get('/{restorant}', 'LoyaltyController@index')->name('index');
            Route::post('/{restorant}/redeem', 'LoyaltyController@redeem')->name('redeem');

            // Owner / staff: report
            Route::get('/report/dashboard', 'LoyaltyReportController@index')->name('report');
        });
    });

});
