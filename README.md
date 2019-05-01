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
* Supported locales : ru, en, ar

## In new version 2.3

In new version you can set default disk and default path

You have two variants for how to do it:

1. Add this params to the config file (config/file-manager.php)

```php
/**
 * Default path for left manager
 * null - root directory
 */
'leftPath'  => 'directory/sub-directory',

/**
 * Default path for right manager
 * null - root directory
 */
'rightPath' => null,
```

2 Or you can add this params in URL

```
http://site.name/?leftDisk=diskName&leftPath=directory
http://site.name/?leftDisk=diskName&leftPath=directory2&rightDisk=diskName2&rightPath=images
```

leftDisk and leftPath is default for the file manager windows configuration - 1,2


## Upgrading to version 2.3

Update pre-compiled css and js files and config file - file-manager.php 


```php
// config
php artisan vendor:publish --tag=fm-config --force
// js, css
php artisan vendor:publish --tag=fm-assets --force
```

You can update the config file manually - add new params:

```php
/**
 * Default path for left manager
 * null - root directory
 */
'leftPath'  => null,

/**
 * Default path for right manager
 * null - root directory
 */
'rightPath' => null,
```


## Thanks

* Khalid Bj [D34DlyM4N](https://github.com/D34DlyM4N)
* NeoSon [lkloon123](https://github.com/lkloon123)


