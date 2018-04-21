<?php

namespace Alexusmai\LaravelFileManager\Services;

use Alexusmai\LaravelFileManager\Traits\HelperTrait;
use League\Flysystem\MountManager;
use Exception;
use Storage;

class ExternalTransferService
{
    use HelperTrait;

    // disk name
    public $disk;

    // path to copy files
    public $path;

    // files and folders to copy / cut
    public $clipboard;

    // MountManager
    public $manager;

    /**
     * ExternalTransferService constructor.
     * @param $disk
     * @param $path
     * @param $clipboard
     */
    public function __construct($disk, $path, $clipboard)
    {
        $this->disk = $disk;
        $this->path = $path;
        $this->clipboard = $clipboard;

        $this->manager = new MountManager([
            'from' => Storage::drive($clipboard['disk'])->getDriver(),
            'to' => Storage::drive($disk)->getDriver()
        ]);
    }

    /**
     * Transfer files and folders to another disk
     * @return array
     */
    public function filesTransfer()
    {
        try {
            // determine the type of operation
            if ($this->clipboard['type'] === 'copy') {

                // files
                foreach ($this->clipboard['files'] as $file) {
                    $this->copyToDisk(
                        $file,
                        $this->renamePath($file, $this->path)
                    );
                }

                // directories
                foreach ($this->clipboard['directories'] as $directory) {
                    $this->copyDirectoryToDisk($directory);
                }

            } elseif ($this->clipboard['type'] === 'cut') {

                // files
                foreach ($this->clipboard['files'] as $file) {
                    $this->moveToDisk(
                        $file,
                        $this->renamePath($file, $this->path)
                    );
                }

                // directories
                foreach ($this->clipboard['directories'] as $directory) {
                    $this->copyDirectoryToDisk($directory);

                    // remove directory
                    Storage::disk($this->clipboard['disk'])->deleteDirectory($directory);
                }

            }
        } catch (Exception $exception) {
            return [
                'result' => [
                    'status'    => 'error',
                    'message'   => $exception->getMessage()
                ]
            ];
        }

        return [
            'result' => [
                'status'    => 'success',
                'message'   => trans('file-manager::response.copied')
            ]
        ];
    }

    /**
     * Copy directory to another disk
     * @param $directory
     */
    protected function copyDirectoryToDisk($directory)
    {
        // get all directories in this directory
        $allDirectories = Storage::disk($this->clipboard['disk'])->allDirectories($directory);

        $partsForRemove = count(explode('/', $directory)) - 1;

        // create this directories
        foreach ($allDirectories as $dir) {
            Storage::disk($this->disk)->makeDirectory(
                    $this->transformPath($dir, $this->path, $partsForRemove)
            );
        }

        // get all files
        $allFiles = Storage::disk($this->clipboard['disk'])->allFiles($directory);

        // copy files
        foreach ($allFiles as $file) {
            $this->copyToDisk($file, $this->transformPath($file, $this->path, $partsForRemove));
        }
    }

    /**
     * Copy files to disk
     * @param $filePath
     * @param $newPath
     */
    protected function copyToDisk($filePath, $newPath)
    {
        $this->manager->copy(
            'from://' . $filePath,
            'to://' . $newPath
        );
    }

    /**
     * Move files to disk
     * @param $filePath
     * @param $newPath
     */
    protected function moveToDisk($filePath, $newPath)
    {
        $this->manager->move(
            'from://' . $filePath,
            'to://' . $newPath
        );
    }
}