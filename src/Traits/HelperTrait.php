<?php

namespace Alexusmai\LaravelFileManager\Traits;

use Storage;

trait HelperTrait {

    /**
     * Check disk name
     * @param $name
     * @return bool
     */
    public function checkDisk($name)
    {
        return in_array($name, config('file-manager.diskList')) &&
            array_key_exists($name, config('filesystems.disks'));
    }

    /**
     * Check Disk and Path
     * @param $disk
     * @param $path
     * @return bool
     */
    public function checkPath($disk, $path)
    {
        // check disk name
        if (! $this->checkDisk($disk)) {
            return false;
        }

        // check path
        if ($path && ! Storage::disk($disk)->exists($path)) {
            return false;
        }

        return true;
    }

    /**
     * Disk/path not found message
     * @return array
     */
    public function notFoundMessage()
    {
        return [
            'result' => [
                'status'    => 'danger',
                'message'   => trans('file-manager::response.notFound')
            ]
        ];
    }

    /**
     * Create path for new directory
     * @param $path
     * @param $name
     * @return string
     */
    public function newDirectoryPath($path, $name)
    {
        if (! $path) {
            return $name;
        }

        return $path . '/' . $name;
    }

    /**
     * Rename path - for copy / cut operations
     * @param $itemPath
     * @param $recipientPath
     * @return string
     */
    public function renamePath($itemPath, $recipientPath)
    {
        if ($recipientPath) {
            return $recipientPath . '/' . basename($itemPath);
        }

        return basename($itemPath);
    }

    /**
     * Transform path name
     * @param $itemPath
     * @param $recipientPath
     * @param $partsForRemove
     * @return string
     */
    public function transformPath($itemPath, $recipientPath, $partsForRemove)
    {
        $elements = array_slice(explode('/', $itemPath), $partsForRemove);

        if ($recipientPath) {
            return $recipientPath . '/' . implode('/', $elements);
        }

        return implode('/', $elements);
    }
}