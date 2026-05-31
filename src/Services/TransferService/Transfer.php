<?php

namespace Alexusmai\LaravelFileManager\Services\TransferService;

use Alexusmai\LaravelFileManager\Traits\FileSecurityTrait;
use Illuminate\Support\Facades\Storage;

abstract class Transfer
{
    use FileSecurityTrait;

    public $disk;
    public $path;
    public $clipboard;

    /**
     * Transfer constructor.
     *
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

    /**
     * Transfer files and folders
     *
     * @return array
     */
    public function filesTransfer(): array
    {
        if ($this->containsDangerousFile()) {
            return [
                'result' => [
                    'status'  => 'warning',
                    'message' => 'dangerousFileType',
                ],
            ];
        }

        try {
            // determine the type of operation
            if ($this->clipboard['type'] === 'copy') {
                $this->copy();
            } elseif ($this->clipboard['type'] === 'cut') {
                $this->cut();
            }
        } catch (\Exception $exception) {
            return [
                'result' => [
                    'status'  => 'error',
                    'message' => $exception->getMessage(),
                ],
            ];
        }

        return [
            'result' => [
                'status'  => 'success',
                'message' => 'copied',
            ],
        ];
    }

    abstract protected function copy();

    abstract protected function cut();

    protected function containsDangerousFile(): bool
    {
        foreach ($this->clipboard['files'] as $file) {
            if ($this->hasDangerousFilename($file)) {
                return true;
            }
        }

        foreach ($this->clipboard['directories'] as $directory) {
            foreach (Storage::disk($this->clipboard['disk'])->allFiles($directory) as $file) {
                if ($this->hasDangerousFilename($file)) {
                    return true;
                }
            }
        }

        return false;
    }
}
