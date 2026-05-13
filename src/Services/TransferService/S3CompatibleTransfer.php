<?php

namespace Alexusmai\LaravelFileManager\Services\TransferService;

use Alexusmai\LaravelFileManager\Traits\MoveFolderTrait;
use Storage;

class S3CompatibleTransfer extends LocalTransfer
{
    use MoveFolderTrait;

    protected function cut()
    {
        $disk = Storage::disk($this->disk);

        // files
        foreach ($this->clipboard['files'] as $file) {
            $disk->move(
                $file,
                $this->renamePath($file, $this->path)
            );
        }

        // directories
        foreach ($this->clipboard['directories'] as $oldPath) {
            $this->moveFolder($disk, $oldPath, $this->renamePath($oldPath, $this->path));
        }
    }
}