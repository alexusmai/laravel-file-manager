<?php

namespace Alexusmai\LaravelFileManager\Traits;

trait FileSecurityTrait
{
    /**
     * Extensions that may be executed by common web server configurations.
     *
     * @var array<int, string>
     */
    protected array $dangerousExtensions = [
        'asp',
        'aspx',
        'cgi',
        'inc',
        'jsp',
        'pgif',
        'phar',
        'php',
        'php2',
        'php3',
        'php4',
        'php5',
        'php6',
        'php7',
        'php8',
        'pht',
        'phtm',
        'phtml',
        'phps',
        'pl',
        'py',
        'shtml',
    ];

    /**
     * Files that can alter how a web server handles uploaded content.
     *
     * @var array<int, string>
     */
    protected array $dangerousFilenames = [
        '.htaccess',
        '.user.ini',
        'web.config',
    ];

    /**
     * MIME types that identify executable PHP content.
     *
     * @var array<int, string>
     */
    protected array $dangerousMimeTypes = [
        'application/x-httpd-php',
        'application/x-php',
        'text/x-php',
    ];

    protected function hasDangerousFilename(string $path): bool
    {
        $filename = strtolower(basename(str_replace('\\', '/', $path)));
        $filename = explode(':', $filename, 2)[0];
        $filename = rtrim($filename, ". \t\n\r\0\x0B");

        if (in_array($filename, $this->dangerousFilenames, true)) {
            return true;
        }

        return (bool) array_intersect(
            explode('.', $filename),
            $this->dangerousExtensions
        );
    }

    protected function hasPathTraversal(string $path): bool
    {
        $path = str_replace('\\', '/', $path);

        return str_starts_with($path, '/')
            || preg_match('/^[a-zA-Z]:\//', $path)
            || in_array('..', explode('/', $path), true);
    }

    protected function hasDangerousMimeType(?string $mimeType): bool
    {
        return $mimeType !== null
            && in_array(strtolower($mimeType), $this->dangerousMimeTypes, true);
    }
}
