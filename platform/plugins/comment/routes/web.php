<?php

Route::group(['namespace' => 'Botble\Comment\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'comments', 'as' => 'comment.'], function () {
            Route::resource('', 'CommentController')->parameters(['' => 'comment']);
            Route::delete('items/destroy', [
                'as'         => 'deletes',
                'uses'       => 'CommentController@deletes',
                'permission' => 'comment.destroy',
            ]);
        });
    });

});
