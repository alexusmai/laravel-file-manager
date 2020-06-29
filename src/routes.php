<?php

use Alexusmai\LaravelFileManager\Services\ConfigService\ConfigRepository;

$config = resolve(ConfigRepository::class);

// App middleware list
$middleware = $config->getMiddleware();

/**
 * If ACL ON add "fm-acl" middleware to array
 */
if ($config->getAcl()) {
    $middleware[] = 'fm-acl';
}

Route::group([
    'middleware' => $middleware,
    'prefix'     => $config->getRoutePrefix(),
    'namespace'  => 'Alexusmai\LaravelFileManager\Controllers',
], function () {

    Route::get('initialize', 'FileManagerController@initialize')
        ->name('fm.initialize');

    Route::get('content', 'FileManagerController@content')
        ->name('fm.content');

    Route::get('tree', 'FileManagerController@tree')
        ->name('fm.tree');

    Route::get('select-disk', 'FileManagerController@selectDisk')
        ->name('fm.select-disk');

    Route::post('upload', 'FileManagerController@upload')
        ->name('fm.upload');

    Route::post('delete', 'FileManagerController@delete')
        ->name('fm.delete');

    Route::post('paste', 'FileManagerController@paste')
        ->name('fm.paste');

    Route::post('rename', 'FileManagerController@rename')
        ->name('fm.rename');

    Route::get('download', 'FileManagerController@download')
        ->name('fm.download');

    Route::get('thumbnails', 'FileManagerController@thumbnails')
        ->name('fm.thumbnails');

    Route::get('preview', 'FileManagerController@preview')
        ->name('fm.preview');

    Route::get('url', 'FileManagerController@url')
        ->name('fm.url');

    Route::post('create-directory', 'FileManagerController@createDirectory')
        ->name('fm.create-directory');

    Route::post('create-file', 'FileManagerController@createFile')
        ->name('fm.create-file');

    Route::post('update-file', 'FileManagerController@updateFile')
        ->name('fm.update-file');

    Route::get('stream-file', 'FileManagerController@streamFile')
        ->name('fm.stream-file');

    Route::post('zip', 'FileManagerController@zip')
        ->name('fm.zip');

    Route::post('unzip', 'FileManagerController@unzip')
        ->name('fm.unzip');

    // Route::get('properties', 'FileManagerController@properties');

    // Integration with editors
    Route::get('ckeditor', 'FileManagerController@ckeditor')
        ->name('fm.ckeditor');

    Route::get('tinymce', 'FileManagerController@tinymce')
        ->name('fm.tinymce');

    Route::get('tinymce5', 'FileManagerController@tinymce5')
        ->name('fm.tinymce5');

    Route::get('summernote', 'FileManagerController@summernote')
        ->name('fm.summernote');

    Route::get('fm-button', 'FileManagerController@fmButton')
        ->name('fm.fm-button');
});
