<?php

Route::group(['namespace' => 'Botble\DiscountCode\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'discount-codes', 'as' => 'discount-code.'], function () {
            Route::resource('', 'DiscountCodeController')->parameters(['' => 'discount-code']);
            Route::delete('items/destroy', [
                'as'         => 'deletes',
                'uses'       => 'DiscountCodeController@deletes',
                'permission' => 'discount-code.destroy',
            ]);
        });
    });

});
