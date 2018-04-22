# Laravel File Manager - Backend

[![Latest Stable Version](https://poser.pugx.org/alexusmai/laravel-file-manager/v/stable)](https://packagist.org/packages/alexusmai/laravel-file-manager)
[![Total Downloads](https://poser.pugx.org/alexusmai/laravel-file-manager/downloads)](https://packagist.org/packages/alexusmai/laravel-file-manager)
[![Latest Unstable Version](https://poser.pugx.org/alexusmai/laravel-file-manager/v/unstable)](https://packagist.org/packages/alexusmai/laravel-file-manager)
[![License](https://poser.pugx.org/alexusmai/laravel-file-manager/license)](https://packagist.org/packages/alexusmai/laravel-file-manager)


![Laravel File Manager](https://raw.github.com/alexusmai/vue-laravel-file-manager/master/src/assets/laravel-file-manager.gif?raw=true)

## Installation

Composer

```
composer require alexusmai/laravel-file-manager
```

If you have Laravel 5.4, then add service provider to config/app.php

```
Alexusmai\LaravelFileManager\FileManagerServiceProvider::class,
```

Publish config file (file-manager.php)

```
php artisan vendor:publish --tag=fm-config
```

> Frontend

You can install npm package directly and use it in your vue application - more information about it -
[vue-laravel-file-manager](https://github.com/alexusmai/vue-laravel-file-manager)

OR

Publish compiled and minimized js and css files

```
php artisan vendor:publish --tag=fm-assets
```

## Settings

Open configuration file - config/file-manager.php

- fill the disk list from config/filesystem.php (select the desired drive names)
- set cache
- select file manager windows configuration

**Be sure to add your middleware to restrict access to the application**


### Open the view file where you want to place the application block, and add:

- add a csrf token to head block if you did not do it before
```html
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
```

- the frontend package uses Bootstrap 4 and Font Awesome 5 styles, if you already use it, then you do not need to connect any styles.
 Otherwise add -
 
```html
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
```

Add file manager styles

```html
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
```

- add file manager js
```html
<script src="{{ asset('vendor/file-manager/css/file-manager.js') }}"></script>
```

- add div for application (set application height!)
```html
<div style="height: 600px;">
    <div id="fm"></div>
</div>
```

## WYSIWYG Editor Integration