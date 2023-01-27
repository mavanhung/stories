<?php

Route::group(['namespace' => 'Botble\Tiki\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        // Route::group(['prefix' => 'tikis', 'as' => 'tiki.'], function () {
        //     Route::resource('', 'TikiController')->parameters(['' => 'tiki']);
        //     Route::delete('items/destroy', [
        //         'as'         => 'deletes',
        //         'uses'       => 'TikiController@deletes',
        //         'permission' => 'tiki.destroy',
        //     ]);
        // });
        Route::group(['prefix' => 'tiki-discount-codes', 'as' => 'tiki_discount_code.'], function () {
            Route::resource('', 'DiscountCodeController')->parameters(['' => 'discount_code']);
            Route::delete('items/destroy', [
                'as'         => 'deletes',
                'uses'       => 'DiscountCodeController@deletes',
                'permission' => 'tiki_discount_code.destroy',
            ]);
        });
        Route::group(['prefix' => 'tiki-sellers', 'as' => 'tiki_seller.'], function () {
            Route::resource('', 'SellerController')->parameters(['' => 'seller']);
            Route::delete('items/destroy', [
                'as'         => 'deletes',
                'uses'       => 'SellerController@deletes',
                'permission' => 'tiki_seller.destroy',
            ]);
        });
    });

    if (defined('THEME_MODULE_SCREEN_NAME')) {
        Route::group(apply_filters(BASE_FILTER_GROUP_PUBLIC_ROUTE, []), function () {
            // Route::get('ma-giam-gia-tiki', [
            //     'as'   => 'public.index',
            //     'uses' => 'PublicController@getIndex',
            // ]);
            Route::group([
                'prefix' => 'ajax'
            ],function() {
                Route::get('tiki-seller', 'PublicController@searchSeller')->name('theme.ajax-tiki-seller');
            });
        });
    }
});
