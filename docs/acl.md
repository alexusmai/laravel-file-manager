## ACL

You can use the access control system to differentiate access to files and folders for different users.
For this you need to make the following settings.
Open configuration file - config/file-manager.php

1. Turn ON ACL system and add fm-acl middleware

    ```php
    // set true
    'acl' => true,
 
    // add acl middleware to your array
    'middleware' => ['web', 'fm-acl'],
    ```

2. You can hide files and folders to which the user does not have access(access = 0).

    ```php
    'aclHideFromFM' => true,
    ```
3. ACL system operation strategies:

    ```php
    /**
     * ACL strategy
     *
     * positive - Allow everything that is not forbidden by the ACL rules list
     * positive access - 2 (r/w)
     *
     * negative - Deny anything, that not allowed by the ACL rules list
     * negative access - 0 (deny)
     */
    'aclStrategy'   => 'positive',
    ```

4. Set the rule repository, the default is the configuration file.
   
   ```php
   /**
    * ACL rules repository
    *
    * default - config file(ConfigACLRepository)
    */
   'aclRepository' => \Alexusmai\LaravelFileManager\ACLService\ConfigACLRepository::class,
   ```
   
   Now you can add your rules in 'aclRules' array. But if you want to store your rules in another place, such as a database, you need to create your own class, and implements two functions from ACLRepository.
   
   I have already made a similar class for an example, and if it suits you, you can use it. You only need to replace the repository name in the configuration file. And add a new migration to the database.
   
   ```php
    php artisan vendor:publish --tag=fm-migrations
    ```
   
   See [/src/ACLService/DBACLRepository.php](./../src/ACLService/DBACLRepository.php) and [/migrations/2019_02_06_174631_make_acl_rules_table.php](./../migrations/2019_02_06_174631_make_acl_rules_table.php)
   
