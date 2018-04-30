<?php

Route::group([
    'middleware'    => config('file-manager.middleware'),
    'prefix'        => 'file-manager',
    'namespace'     => 'Alexusmai\LaravelFileManager\Controllers'
], function (){

    Route::get('initialize', 'FileManagerController@initialize')->name('fm.initialize');

    Route::get('content', 'FileManagerController@content')->name('fm.content');

    Route::get('tree', 'FileManagerController@tree')->name('fm.tree');

    Route::get('select-disk', 'FileManagerController@selectDisk')->name('fm.selectDisk');

    Route::post('create-directory', 'FileManagerController@createDirectory')->name('fm.createDirectory');

    Route::post('upload', 'FileManagerController@upload')->name('fm.upload');

    Route::post('delete', 'FileManagerController@delete')->name('fm.delete');

    Route::post('paste', 'FileManagerController@paste')->name('fm.paste');

    Route::post('rename', 'FileManagerController@rename')->name('fm.rename');

    Route::get('download', 'FileManagerController@download')->name('fm.download');

    Route::get('properties', 'FileManagerController@properties')->name('fm.properties');

    Route::get('thumbnails', 'FileManagerController@thumbnails')->name('fm.thumbnails');

    Route::get('preview', 'FileManagerController@preview')->name('fm.preview');

    Route::get('url', 'FileManagerController@url')->name('fm.url');

    // Integration with editors
    Route::get('ckeditor', 'FileManagerController@ckeditor')->name('fm.ckeditor');
});