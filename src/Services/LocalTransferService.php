<?php

namespace Alexusmai\LaravelFileManager\Services;

use Alexusmai\LaravelFileManager\Traits\HelperTrait;
use Exception;
use Storage;

class LocalTransferService
{
    use HelperTrait;

    // disk name
    public $disk;

    // path to copy files
    public $path;

    // files and folders to copy / cut
    public $clipboard;

    /**
     * LocalTransferService constructor.
     * @param $disk
     * @param $path
     * @param $clipboard
     */
    public function __construct($disk, $path, $clipboard)
    {
        $this->disk = $disk;
        $this->path = $path;
        $this->clipboard = $clipboard;
    }

    public function filesTransfer()
    {
        try {

            // determine the type of operation
            if ($this->clipboard['type'] === 'copy') {

                // files
                foreach ($this->clipboard['files'] as $file) {
                    Storage::disk($this->disk)->copy(
                        $file,
                        $this->renamePath($file, $this->path)
                    );
                }

                // directories
                foreach ($this->clipboard['directories'] as $directory) {
                    $this->copyDirectory($directory);
                }

            } elseif ($this->clipboard['type'] === 'cut') {

                // files
                foreach ($this->clipboard['files'] as $file) {
                    Storage::disk($this->disk)->move(
                        $file,
                        $this->renamePath($file, $this->path)
                    );
                }

                // directories
                foreach ($this->clipboard['directories'] as $directory) {
                    Storage::disk($this->disk)->move(
                        $directory,
                        $this->renamePath($directory, $this->path)
                    );
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
     * Copy directory
     * @param $directory
     */
    protected function copyDirectory($directory)
    {
        // get all directories in this directory
        $allDirectories = Storage::disk($this->disk)->allDirectories($directory);

        $partsForRemove = count(explode('/', $directory)) - 1;

        // create this directories
        foreach ($allDirectories as $dir) {
            Storage::disk($this->disk)->makeDirectory(
                $this->transformPath(
                    $dir,
                    $this->path,
                    $partsForRemove
                )
            );
        }

        // get all files
        $allFiles = Storage::disk($this->disk)->allFiles($directory);

        // copy files
        foreach ($allFiles as $file) {
            Storage::disk($this->disk)
                ->copy($file, $this->transformPath($file, $this->path, $partsForRemove));
        }
    }
}