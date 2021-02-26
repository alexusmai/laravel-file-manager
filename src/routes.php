<?php

use Alexusmai\LaravelFileManager\Services\ConfigService\ConfigRepository;

$config = resolve(ConfigRepository::class);

// App middleware list
$middleware = $config->getMiddleware();

$controller = $config->getController();

/**
 * If ACL ON add "fm-acl" middleware to array
 */
if ($config->getAcl()) {
    $middleware[] = 'fm-acl';
}

Route::group([
    'middleware' => $middleware,
    'prefix'     => $config->getRoutePrefix(),
], function () use ($controller) {

    Route::get('initialize', $controller.'@initialize')
        ->name('fm.initialize');

    Route::get('content', $controller.'@content')
        ->name('fm.content');

    Route::get('tree', $controller.'@tree')
        ->name('fm.tree');

    Route::get('select-disk', $controller.'@selectDisk')
        ->name('fm.select-disk');

    Route::post('upload', $controller.'@upload')
        ->name('fm.upload');

    Route::post('delete', $controller.'@delete')
        ->name('fm.delete');

    Route::post('paste', $controller.'@paste')
        ->name('fm.paste');

    Route::post('rename', $controller.'@rename')
        ->name('fm.rename');

    Route::get('download', $controller.'@download')
        ->name('fm.download');

    Route::get('thumbnails', $controller.'@thumbnails')
        ->name('fm.thumbnails');

    Route::get('preview', $controller.'@preview')
        ->name('fm.preview');

    Route::get('url', $controller.'@url')
        ->name('fm.url');

    Route::post('create-directory', $controller.'@createDirectory')
        ->name('fm.create-directory');

    Route::post('create-file', $controller.'@createFile')
        ->name('fm.create-file');

    Route::post('update-file', $controller.'@updateFile')
        ->name('fm.update-file');

    Route::get('stream-file', $controller.'@streamFile')
        ->name('fm.stream-file');

    Route::post('zip', $controller.'@zip')
        ->name('fm.zip');

    Route::post('unzip', $controller.'@unzip')
        ->name('fm.unzip');

    // Route::get('properties', $controller.'@properties');

    // Integration with editors
    Route::get('ckeditor', $controller.'@ckeditor')
        ->name('fm.ckeditor');

    Route::get('tinymce', $controller.'@tinymce')
        ->name('fm.tinymce');

    Route::get('tinymce5', $controller.'@tinymce5')
        ->name('fm.tinymce5');

    Route::get('summernote', $controller.'@summernote')
        ->name('fm.summernote');

    Route::get('fm-button', $controller.'@fmButton')
        ->name('fm.fm-button');
});
