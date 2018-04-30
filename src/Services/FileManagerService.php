<?php

namespace Alexusmai\LaravelFileManager\Services;

use Alexusmai\LaravelFileManager\Traits\ContentTrait;
use Alexusmai\LaravelFileManager\Traits\HelperTrait;
use Illuminate\Support\Str;
use Storage;
use Image;
use App;

class FileManagerService
{
    use HelperTrait, ContentTrait;

    /**
     * Initialize App
     * @return array
     */
    public function initialize()
    {
        $config = config('file-manager');
        // get language
        $config['lang'] = App::getLocale();

        // if config not found
        if (! $config) {
            return [
                'result' => [
                    'status'    => 'danger',
                    'message'   => trans('file-manager::response.noConfig')
                ]
            ];
        }

        return [
            'result' => [
                'status'    => 'success',
                'message'   => null
            ],
            'config' => $config
        ];
    }

    /**
     * Get files and directories for the selected path and disk
     * @param $disk
     * @param $path
     * @return array
     */
    public function content($disk, $path)
    {
        if (! $this->checkPath($disk, $path)) {
            return $this->notFoundMessage();
        }

        // get content for the selected directory
        $content = $this->getContent($disk, $path);

        return [
            'result' => [
                'status'    => 'success',
                'message'   => null
            ],
            'directories'   => $content['directories'],
            'files'         => $content['files']
        ];
    }

    /**
     * Get part of the directory tree
     * @param $disk
     * @param $path
     * @return array
     */
    public function tree($disk, $path)
    {
        if (! $this->checkPath($disk, $path)) {
            return $this->notFoundMessage();
        }

        // get directories for the directories tree
        $directories = $this->getDirectoriesTree($disk, $path);

        return [
            'result' => [
                'status'    => 'success',
                'message'   => null
            ],
            'directories' => $directories
        ];
    }

    /**
     * Check the selected disk
     * @param $disk
     * @return array
     */
    public function selectDisk($disk)
    {
        if (! $this->checkDisk($disk)) {
            return $this->notFoundMessage();
        }

        return [
            'result' => [
                'status'    => 'success',
                'message'   => trans('file-manager::response.diskSelected')
            ]
        ];
    }

    /**
     * Create new directory
     * @param $disk
     * @param $path
     * @param $name
     * @return array
     */
    public function createDirectory($disk, $path, $name)
    {
        if (! $this->checkDisk($disk)) {
            return $this->notFoundMessage();
        }

        // path for new directory
        $directoryName = $this->newDirectoryPath($path, $name);

        // check - exist directory or no
        if (Storage::disk($disk)->exists($directoryName)) {
            return [
                'result' => [
                    'status'    => 'warning',
                    'message'   => trans('file-manager::response.dirExist')
                ]
            ];
        }

        // create new directory
        Storage::disk($disk)->makeDirectory($directoryName);

        // get directory properties
        $directoryProperties = $this->directoryProperties($disk, $directoryName);

        // add directory properties for the tree module
        $tree = $directoryProperties;
        $tree['props'] = ['hasSubdirectories' => false];

        return [
            'result' => [
                'status'    => 'success',
                'message'   => trans('file-manager::response.dirCreated')
            ],
            'directory' => $directoryProperties,
            'tree'      => [$tree]
        ];
    }

    /**
     * Upload files
     * @param $disk
     * @param $path
     * @param $files
     * @param $overwrite
     * @return array
     */
    public function upload($disk, $path, $files, $overwrite)
    {
        if (! $this->checkPath($disk, $path)) {
            return $this->notFoundMessage();
        }

        foreach ($files as $file) {
            // skip or overwrite files
            if (! $overwrite) {
                // if file exist, take next file
                if (Storage::disk($disk)->exists($path . '/' . $file->getClientOriginalName())) continue;
            }

            // overwrite or save file
            Storage::disk($disk)->putFileAs(
                $path,
                $file,
                $file->getClientOriginalName()
            );
        }

        return [
            'result' => [
                'status'    => 'success',
                'message'   => trans('file-manager::response.uploaded')
            ]
        ];
    }

    /**
     * Delete files and folders
     * @param $disk
     * @param $items
     * @return array
     */
    public function delete($disk, $items)
    {
        if (! $this->checkDisk($disk)) {
            return $this->notFoundMessage();
        }

        // check all files and folders - exists or no
        $allItemsExists = true;

        foreach ($items as $item) {
            if (! Storage::disk($disk)->exists($item['path'])) {
                $allItemsExists = false;
            }
        }

        if (! $allItemsExists) {
            return [
                'result' => [
                    'status'    => 'danger',
                    'message'   => trans('file-manager::response.delNotFound')
                ]
            ];
        }

        // delete files and folders
        foreach ($items as $item) {
            if ($item['type'] === 'dir') {
                // delete directory
                Storage::disk($disk)->deleteDirectory($item['path']);
            } else {
                // delete file
                Storage::disk($disk)->delete($item['path']);
            }
        }

        return [
            'result' => [
                'status'    => 'success',
                'message'   => trans('file-manager::response.deleted')
            ]
        ];
    }

    /**
     * Copy / Cut - Files and Directories
     * @param $disk
     * @param $path
     * @param $clipboard
     * @return array
     */
    public function paste($disk, $path, $clipboard)
    {
        if (! $this->checkDisk($disk)) {
            return $this->notFoundMessage();
        }

        // compare disk names
        if ($disk !== $clipboard['disk']) {

            if (! $this->checkDisk($clipboard['disk'])) {
                return $this->notFoundMessage();
            }

            $external = new ExternalTransferService($disk, $path, $clipboard);

            return $external->filesTransfer();
        }

        $local = new LocalTransferService($disk, $path, $clipboard);

        return $local->filesTransfer();
    }

    /**
     * Rename file or folder
     * @param $disk
     * @param $newName
     * @param $oldName
     * @return array
     */
    public function rename($disk, $newName, $oldName)
    {
        if (! $this->checkPath($disk, $oldName)) {
            return $this->notFoundMessage();
        }

        // rename
        Storage::disk($disk)->move($oldName, $newName);

        return [
            'result' => [
                'status'    => 'success',
                'message'   => trans('file-manager::response.renamed')
            ]
        ];
    }

    /**
     * Download selected file
     * @param $disk
     * @param $path
     * @return mixed
     */
    public function download($disk, $path)
    {
        // disk or path not found
        if (! $this->checkPath($disk, $path)) {
            abort(404, trans('file-manager::response.fileNotFound'));
        }

        // if file name not in ASCII format
        if (! preg_match('/^[\x20-\x7e]*$/', basename($path))) {
            return Storage::disk($disk)->download($path, Str::ascii(basename($path)));
        }

        return Storage::disk($disk)->download($path);
    }

    /**
     * Create thumbnails
     * @param $disk
     * @param $path
     * @return \Illuminate\Http\Response
     */
    public function thumbnails($disk, $path)
    {
        // disk or path not found
        if (! $this->checkPath($disk, $path)) {
            abort(404, trans('file-manager::response.fileNotFound'));
        }

        // create thumbnail
        if (config('file-manager.cache')) {
            $thumbnail = Image::cache(function($image) use ($disk, $path){
                $image->make(
                    Storage::disk($disk)->get($path)
                )->fit(80);
            }, config('file-manager.cache'));

            // output
            return response()->make(
                $thumbnail,
                200,
                ['Content-Type' => Storage::disk($disk)->mimeType($path)]
            );
        } else {
            $thumbnail = Image::make(Storage::disk($disk)->get($path))->fit(80);

            return $thumbnail->response();
        }


    }

    /**
     * Image preview
     * @param $disk
     * @param $path
     * @return mixed
     */
    public function preview($disk, $path)
    {
        // disk or path not found
        if (! $this->checkPath($disk, $path)) {
            abort(404, trans('file-manager::response.fileNotFound'));
        }

        // get image
        $preview = Image::make(Storage::disk($disk)->get($path));

        return $preview->response();
    }

    /**
     * Get file URL
     * @param $disk
     * @param $path
     * @return array
     */
    public function url($disk, $path)
    {
        if (! $this->checkPath($disk, $path)) {
            return $this->notFoundMessage();
        }

        $url = Storage::disk($disk)->url($path);

        // get image
        return [
            'result' => [
                'status'    => 'success',
                'message'   => null
            ],
            'url' => $url
        ];
    }
}