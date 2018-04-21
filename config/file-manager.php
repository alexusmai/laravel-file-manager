<?php

return [

    /**
     * list of disk names that you want to use
     * (from config/filesystems)
     */
    'diskList'  => ['public'],

    /**
     * Default disk for left manager
     * null - auto select the first disk in the disk list
     */
    'leftDisk'  => null,

    /**
     * Default disk for right manager
     * null - auto select the first disk in the disk list
     */
    'rightDisk' => null,

    /**
     * Image cache ( Intervention Image Cache )
     * set null, 0 - if you don't need cache (default)
     * if you want use cache - set the number of minutes for which the value should be cached
     */
    'cache' => null,

    /**
     * File manager modules configuration
     * 1 - only one file manager window
     * 2 - one file manager window with directories tree module
     * 3 - two file manager windows
     */
    'windowsConfig' => 2,

    /**
     * Middleware
     * Add your middleware name to array -> ['web', 'auth', 'admin']
     * !!!! RESTRICT ACCESS FOR NON ADMIN USERS !!!!
     */
    'middleware'    => ['web', 'auth']
];