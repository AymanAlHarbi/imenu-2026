<?php

Route::group([
    'middleware' => ['auth:sanctum'],
    'namespace' => 'Modules\LoyaltyPoints\Http\Controllers',
    'prefix' => 'api/loyalty',
], function () {
    Route::get('/{restorant}/balance', 'LoyaltyApiController@balance')->name('loyaltypoints.api.balance');
    Route::get('/{restorant}/history', 'LoyaltyApiController@history')->name('loyaltypoints.api.history');
});
