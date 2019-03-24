# Configuration

Open configuration file - config/file-manager.php

- fill the disk list from config/filesystem.php (select the desired drive names)
- set cache
- select file manager windows configuration

**!!! Be sure to add your middleware to restrict access to the application !!!**

**Don't forget to configure your php and Nginx**

```
// PHP
upload_max_filesize,
post_max_size

// Nginx
client_max_body_size
```

## Disk settings example

- config/filesystems.php

```php
// Filesystem Disks
'disks' => [
    // images folder in public path
    'images' => [
        'driver' => 'local',
        'root' => public_path('images'),
        'url' => env('APP_URL').'/images',
    ],

    // public folder in storage/app/public
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage', // https://laravel.com/docs/5.7/filesystem#file-urls
        'visibility' => 'public',
    ],

    // ftp
    'dd-wrt' => [
        'driver'   => 'ftp',
        'host'     => 'ftp.dd-wrt.com',
        'username' => 'anonymous',
        'passive'  => true,
        'timeout'  => 30,
    ],
],
```

- config/file-manager.php

```php
// You need to enter the disks you want to use in the file manager
'diskList'  => ['images', 'public'],
```

>If you want to change default disk at runtime, you can add search params to the URL:

```
// !!! The disk must be in diskList !!!

// one window
http://site-name.com/your-url?leftDisk=public

// two windows
http://site-name.com/your-url?leftDisk=public&rightDisk=images
```

## What's next

[ACL](./acl.md)

[Integration](./integration.md)
