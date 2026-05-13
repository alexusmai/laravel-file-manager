<?php

namespace Alexusmai\LaravelFileManager\Traits;

use Illuminate\Filesystem\FilesystemAdapter;
use Storage;

trait MoveFolderTrait
{

    /**
     * S3 and other remote disks often do not support move() for folders.
     * This method adds a way to move a folder by a simple process of making
     * the new folder and moving the old contents over, then removing the
     * old folder.
     *
     * @param string|FilesystemAdapter $disk The disk to perform the operation on.
     * @param string $oldPath
     * @param string $newPath
     */
    protected function moveFolder(string|FilesystemAdapter $disk, string $oldPath, string $newPath): void
    {
        $storage = $disk instanceof FilesystemAdapter
            ? $disk
            : Storage::disk($disk);

        // If we're renaming a folder, we have to recreate the whole
        // structure...
        $storage->makeDirectory($newPath);

        // Ensure all subfolders are built (including empty).
        foreach ($storage->allDirectories($oldPath) as $path) {
            $storage->makeDirectory(str_replace($oldPath, $newPath, $path));
        }

        // Now move all files over...
        foreach ($storage->allFiles($oldPath) as $file) {
            $storage->move($file, str_replace($oldPath, $newPath, $file));
        }

        // And finish by cleaning up the old folders.
        $storage->deleteDirectory($oldPath);
    }

}