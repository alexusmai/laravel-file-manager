<?php

namespace Alexusmai\LaravelFileManager\Services\TransferService;

use Alexusmai\LaravelFileManager\FileManager;

class TransferFactory
{
    /**
     * @param $disk
     * @param $path
     * @param $clipboard
     *
     * @return ExternalTransfer|S3CompatibleTransfer|LocalTransfer
     */
    public static function build($disk, $path, $clipboard)
    {
        if ($disk !== $clipboard['disk']) {
            return new ExternalTransfer($disk, $path, $clipboard);
        }

        if (FileManager::getDiskDriver($disk) === 's3') {
            return new S3CompatibleTransfer($disk, $path, $clipboard);
        }

        return new LocalTransfer($disk, $path, $clipboard);
    }
}
