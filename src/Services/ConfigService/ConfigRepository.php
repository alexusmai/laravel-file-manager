<?php

namespace Alexusmai\LaravelFileManager\Services\ConfigService;

/**
 * Interface ConfigRepository
 *
 * @package Alexusmai\LaravelFileManager\Services\ConfigService
 */
interface ConfigRepository
{
    /**
     * LFM Route prefix
     * !!! WARNING - if you change it, you should compile frontend with new prefix(baseUrl) !!!
     *
     * @return string
     */
    public function getRoutePrefix(): string;

    /**
     * Get disk list
     *
     * ['public', 'local', 's3']
     *
     * @return array
     */
    public function getDiskList(): array;

    /**
     * Default disk for left manager
     *
     * null - auto select the first disk in the disk list
     *
     * @return string|null
     */
    public function getLeftDisk(): ?string;

    /**
     * Default disk for right manager
     *
     * null - auto select the first disk in the disk list
     *
     * @return string|null
     */
    public function getRightDisk(): ?string;

    /**
     * Default path for left manager
     *
     * null - root directory
     *
     * @return string|null
     */
    public function getLeftPath(): ?string;

    /**
     * Default path for right manager
     *
     * null - root directory
     *
     * @return string|null
     */
    public function getRightPath(): ?string;

    /**
     * File manager modules configuration
     *
     * 1 - only one file manager window
     * 2 - one file manager window with directories tree module
     * 3 - two file manager windows
     *
     * @return int
     */
    public function getWindowsConfig(): int;

    /**
     * File upload - Max file size in KB
     *
     * null - no restrictions
     */
    public function getMaxUploadFileSize(): ?int;

    /**
     * File upload - Allow these file types
     *
     * [] - no restrictions
     */
    public function getAllowFileTypes(): array;

    /**
     * File upload - disallow these executable file types
     *
     * [] - no restrictions
     */
    public function getDisallowFileTypes(): array;

    /**
     * File upload - disallow these executable file mimetypes
     *
     * [] - no restrictions
     */
    public function getDisallowFileMimeTypes(): array;

    /**
     * Show / Hide system files and folders
     *
     * @return bool
     */
    public function getHiddenFiles(): bool;

    /**
     * Middleware
     *
     * Add your middleware name to array -> ['web', 'auth', 'admin']
     * !!!! RESTRICT ACCESS FOR NON ADMIN USERS !!!!
     *
     * @return array
     */
    public function getMiddleware(): array;

    /**
     * ACL mechanism ON/OFF
     *
     * default - false(OFF)
     *
     * @return bool
     */
    public function getAcl(): bool;

    /**
     * Hide files and folders from file-manager if user doesn't have access
     *
     * ACL access level = 0
     *
     * @return bool
     */
    public function getAclHideFromFM(): bool;

    /**
     * ACL strategy
     *
     * blacklist - Allow everything(access - 2 - r/w) that is not forbidden by the ACL rules list
     *
     * whitelist - Deny anything(access - 0 - deny), that not allowed by the ACL rules list
     *
     * @return string
     */
    public function getAclStrategy(): string;

    /**
     * ACL rules repository
     *
     * default - config file(ConfigACLRepository)
     *
     * @return string
     */
    public function getAclRepository(): string;

    /**
     * ACL Rules cache
     *
     * null or value in minutes
     *
     * @return int|null
     */
    public function getAclRulesCache(): ?int;

    /**
     * Whether to slugify filenames
     *
     * boolean
     *
     * @return bool|null
     */
    public function getSlugifyNames(): ?bool;
}
