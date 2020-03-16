<?php

Route::group(['namespace' => 'Botble\Comments\Http\Controllers', 'middleware' => 'web'], function () {

    Route::group(['prefix' => config('core.base.general.admin_dir'), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'comments', 'as' => 'comments.'], function () {
            Route::resource('', 'CommentsController')->parameters(['' => 'comments']);
            Route::delete('items/destroy', [
                'as'         => 'deletes',
                'uses'       => 'CommentsController@deletes',
                'permission' => 'comments.destroy',
            ]);
        });
    });

});
