<?php

Route::group([
    'middleware' => ['web', 'auth', 'impersonate'],
    'namespace' => 'Modules\LoyaltyPoints\Http\Controllers',
], function () {

    Route::prefix('loyalty')->group(function () {
        Route::name('loyaltypoints.')->group(function () {
            // Client: hub of all restaurants they have points with
            Route::get('/', 'LoyaltyController@myAccounts')->name('mine');

            // Client: view points + redeem for a specific restorant
            Route::get('/{restorant}', 'LoyaltyController@index')->name('index');
            Route::post('/{restorant}/redeem', 'LoyaltyController@redeem')->name('redeem');

            // Owner / staff: report
            Route::get('/report/dashboard', 'LoyaltyReportController@index')->name('report');
            Route::get('/report/run-now', 'LoyaltyReportController@runNow')->name('report.run');
        });
    });

});
