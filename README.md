# Laravel File Manager

[![Latest Stable Version](https://poser.pugx.org/alexusmai/laravel-file-manager/v/stable)](https://packagist.org/packages/alexusmai/laravel-file-manager)
[![Total Downloads](https://poser.pugx.org/alexusmai/laravel-file-manager/downloads)](https://packagist.org/packages/alexusmai/laravel-file-manager)
[![Latest Unstable Version](https://poser.pugx.org/alexusmai/laravel-file-manager/v/unstable)](https://packagist.org/packages/alexusmai/laravel-file-manager)
[![License](https://poser.pugx.org/alexusmai/laravel-file-manager/license)](https://packagist.org/packages/alexusmai/laravel-file-manager)


![Laravel File Manager](https://raw.github.com/alexusmai/vue-laravel-file-manager/master/src/assets/laravel-file-manager.gif?raw=true)

**DEMO:** [Laravel File Manager](http://file-manager.webmai.ru/)

**Vue.js Frontend:** [alexusmai/vue-laravel-file-manager](https://github.com/alexusmai/vue-laravel-file-manager)

## Documentation

[Laravel File Manager Docs](./docs/index.md)
* [Installation](./docs/installation.md)
* [Configuration](./docs/configuration.md)
* [Integration](./docs/integration.md)
* [ACL](./docs/acl.md)
* [Events](./docs/events.md)

## Features

* Frontend on Vue.js - [vue-laravel-file-manager](https://github.com/alexusmai/vue-laravel-file-manager)
* Work with the file system is organized by the standard means Laravel Flysystem:
  * Local, FTP, S3, Dropbox ...
  * The ability to work only with the selected disks
* Several options for displaying the file manager:
  * One-panel view
  * One-panel + Directory tree
  * Two-panel
* The minimum required set of operations:
   * Creating files
   * Creating folders
   * Copying / Cutting Folders and Files
   * Renaming
   * Uploading files (multi-upload)
   * Downloading files
   * Two modes of displaying elements - table and grid
   * Preview for images
   * Viewing images
   * Full screen mode
* More operations (v.2):
   * Audio player (mp3, ogg, wav, aac), Video player (webm, mp4) - ([Plyr](https://github.com/sampotts/plyr))
   * Code editor - ([Code Mirror](https://github.com/codemirror/codemirror))
   * Image cropper - ([Cropper.js](https://github.com/fengyuanchen/cropperjs))
   * Zip / Unzip - only for local disks
* Integration with WYSIWYG Editors:
  * CKEditor 4
  * TinyMCE 4
  * TinyMCE 5
  * SummerNote
  * Standalone button
* ACL - access control list
  * delimiting access to files and folders
  * two work strategies:
    * blacklist - Allow everything that is not forbidden by the ACL rules list
    * whitelist - Deny everything, that not allowed by the ACL rules list
  * You can use different repositories for the rules - an array (configuration file), a database (there is an example implementation), or you can add your own.
  * You can hide files and folders that are not accessible.
* Events (v2.2)
* Thumbnails lazy load
* Dynamic configuration (v2.4)
* Supported locales : ru, en, ar, sr, cs

## Laravel 6

#### For Laravel 6 - You need to install - String & Array Helpers Package

```php
composer require laravel/helpers
```

## In a new version 2.4

Now you can create your own config repositories, it will allow to change your configuration dynamically.

How to do it:

Create new class - example - TestConfigRepository

```php
namespace App\Http;

use Alexusmai\LaravelFileManager\Services\ConfigService\ConfigRepository;

class TestConfigRepository implements ConfigRepository
{
    // implement all methods from interface
}
```

For example see [src/Services/ConfigService/DefaultConfigRepository.php](https://github.com/alexusmai/laravel-file-manager/blob/master/src/Services/ConfigService/DefaultConfigRepository.php)

## Upgrading to version 2.4

Update pre-compiled css and js files and config file - file-manager.php 


```php
// config
php artisan vendor:publish --tag=fm-config --force
// js, css
php artisan vendor:publish --tag=fm-assets --force
```

If you use the ACL, now you don't need to add the acl middleware to configuration.

```php
//======= In old versions ==========
'acl' => true,

// add acl middleware to your array
'middleware' => ['web', 'fm-acl'],

//======= In a new version =========
'acl' => true,

'middleware' => ['web'],
```

## Thanks

* Khalid Bj [D34DlyM4N](https://github.com/D34DlyM4N)
* NeoSon [lkloon123](https://github.com/lkloon123)
* Aleksandar Stevanović [aleks989](https://github.com/aleks989)
* Aleš Nejdr [mige](https://github.com/mige)


