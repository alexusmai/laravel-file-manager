<?php

Route::group([
    'middleware'    => config('file-manager.middleware'),
    'prefix'        => 'file-manager',
    'namespace'     => 'Alexusmai\LaravelFileManager\Controllers'
], function (){

    Route::get('initialize', 'FileManagerController@initialize');

    Route::get('content', 'FileManagerController@content');

    Route::get('tree', 'FileManagerController@tree');

    Route::get('select-disk', 'FileManagerController@selectDisk');

    Route::post('create-directory', 'FileManagerController@createDirectory');

    Route::post('upload', 'FileManagerController@upload');

    Route::post('delete', 'FileManagerController@delete');

    Route::post('paste', 'FileManagerController@paste');

    Route::post('rename', 'FileManagerController@rename');

    Route::get('download', 'FileManagerController@download');

    Route::get('properties', 'FileManagerController@properties');

    Route::get('thumbnails', 'FileManagerController@thumbnails');

    Route::get('preview', 'FileManagerController@preview');

    Route::get('url', 'FileManagerController@url');

    // Integration with editors
    Route::get('ckeditor', 'FileManagerController@ckeditor');
});