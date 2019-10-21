<?php

namespace Alexusmai\LaravelFileManager\Traits;

use Storage;

trait CheckTrait
{

    /**
     * Check disk name
     *
     * @param $name
     *
     * @return bool
     */
    public function checkDisk($name)
    {
        return in_array($name, $this->configRepository->getDiskList())
            && array_key_exists($name, config('filesystems.disks'));
    }

    /**
     * Check Disk and Path
     *
     * @param $disk
     * @param $path
     *
     * @return bool
     */
    public function checkPath($disk, $path)
    {
        // check disk name
        if (!$this->checkDisk($disk)) {
            return false;
        }

        // check path
        if ($path && !Storage::disk($disk)->exists($path)) {
            return false;
        }

        return true;
    }

    /**
     * Disk/path not found message
     *
     * @return array
     */
    public function notFoundMessage()
    {
        return [
            'result' => [
                'status'  => 'danger',
                'message' => 'notFound',
            ],
        ];
    }
}
