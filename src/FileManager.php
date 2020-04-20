<?php

namespace Alexusmai\LaravelFileManager;

use Alexusmai\LaravelFileManager\Events\Deleted;
use Alexusmai\LaravelFileManager\Traits\CheckTrait;
use Alexusmai\LaravelFileManager\Traits\ContentTrait;
use Alexusmai\LaravelFileManager\Traits\PathTrait;
use Alexusmai\LaravelFileManager\Services\TransferService\TransferFactory;
use Alexusmai\LaravelFileManager\Services\ConfigService\ConfigRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Storage;
use Image;

class FileManager
{
    use PathTrait, ContentTrait, CheckTrait;

    /**
     * @var ConfigRepository
     */
    public $configRepository;

    /**
     * FileManager constructor.
     *
     * @param  ConfigRepository  $configRepository
     */
    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    /**
     * Initialize App
     *
     * @return array
     */
    public function initialize()
    {
        // if config not found
        if (!config()->has('file-manager')) {
            return [
                'result' => [
                    'status'  => 'danger',
                    'message' => 'noConfig'
                ],
            ];
        }

        $config = [
            'acl'           => $this->configRepository->getAcl(),
            'leftDisk'      => $this->configRepository->getLeftDisk(),
            'rightDisk'     => $this->configRepository->getRightDisk(),
            'leftPath'      => $this->configRepository->getLeftPath(),
            'rightPath'     => $this->configRepository->getRightPath(),
            'windowsConfig' => $this->configRepository->getWindowsConfig(),
            'hiddenFiles'   => $this->configRepository->getHiddenFiles(),
        ];

        // disk list
        foreach ($this->configRepository->getDiskList() as $disk) {
            if (array_key_exists($disk, config('filesystems.disks'))) {
                $config['disks'][$disk] = Arr::only(
                    config('filesystems.disks')[$disk], ['driver']
                );
            }
        }

        // get language
        $config['lang'] = app()->getLocale();

        return [
            'result' => [
                'status'  => 'success',
                'message' => null,
            ],
            'config' => $config,
        ];
    }

    /**
     * Get files and directories for the selected path and disk
     *
     * @param $disk
     * @param $path
     *
     * @return array
     */
    public function content($disk, $path)
    {
        // get content for the selected directory
        $content = $this->getContent($disk, $path);

        return [
            'result'      => [
                'status'  => 'success',
                'message' => null,
            ],
            'directories' => $content['directories'],
            'files'       => $content['files'],
        ];
    }

    /**
     * Get part of the directory tree
     *
     * @param $disk
     * @param $path
     *
     * @return array
     */
    public function tree($disk, $path)
    {
        $directories = $this->getDirectoriesTree($disk, $path);

        return [
            'result'      => [
                'status'  => 'success',
                'message' => null,
            ],
            'directories' => $directories,
        ];
    }

    /**
     * Upload files
     *
     * @param $disk
     * @param $path
     * @param $files
     * @param $overwrite
     *
     * @return array
     */
    public function upload($disk, $path, $files, $overwrite)
    {
        $fileNotUploaded = false;

        foreach ($files as $file) {
            // skip or overwrite files
            if (!$overwrite
                && Storage::disk($disk)
                    ->exists($path.'/'.$file->getClientOriginalName())
            ) {
                continue;
            }

            // check file size if need
            if ($this->configRepository->getMaxUploadFileSize()
                && $file->getSize() / 1024 > $this->configRepository->getMaxUploadFileSize()
            ) {
                $fileNotUploaded = true;
                continue;
            }

            // check file type if need
            if ($this->configRepository->getAllowFileTypes()
                && !in_array(
                    $file->getClientOriginalExtension(),
                    $this->configRepository->getAllowFileTypes()
                )
            ) {
                $fileNotUploaded = true;
                continue;
            }

            // overwrite or save file
            Storage::disk($disk)->putFileAs(
                $path,
                $file,
                $file->getClientOriginalName()
            );
        }

        // If the some file was not uploaded
        if ($fileNotUploaded) {
            return [
                'result' => [
                    'status'  => 'warning',
                    'message' => 'notAllUploaded',
                ],
            ];
        }

        return [
            'result' => [
                'status'  => 'success',
                'message' => 'uploaded',
            ],
        ];
    }

    /**
     * Delete files and folders
     *
     * @param $disk
     * @param $items
     *
     * @return array
     */
    public function delete($disk, $items)
    {
        $deletedItems = [];

        foreach ($items as $item) {
            // check all files and folders - exists or no
            if (!Storage::disk($disk)->exists($item['path'])) {
                continue;
            } else {
                if ($item['type'] === 'dir') {
                    // delete directory
                    Storage::disk($disk)->deleteDirectory($item['path']);
                } else {
                    // delete file
                    Storage::disk($disk)->delete($item['path']);
                }
            }

            // add deleted item
            $deletedItems[] = $item;
        }

        event(new Deleted($disk, $deletedItems));

        return [
            'result' => [
                'status'  => 'success',
                'message' => 'deleted',
            ],
        ];
    }

    /**
     * Copy / Cut - Files and Directories
     *
     * @param $disk
     * @param $path
     * @param $clipboard
     *
     * @return array
     */
    public function paste($disk, $path, $clipboard)
    {
        // compare disk names
        if ($disk !== $clipboard['disk']) {

            if (!$this->checkDisk($clipboard['disk'])) {
                return $this->notFoundMessage();
            }
        }

        $transferService = TransferFactory::build($disk, $path, $clipboard);

        return $transferService->filesTransfer();
    }

    /**
     * Rename file or folder
     *
     * @param $disk
     * @param $newName
     * @param $oldName
     *
     * @return array
     */
    public function rename($disk, $newName, $oldName)
    {
        Storage::disk($disk)->move($oldName, $newName);

        return [
            'result' => [
                'status'  => 'success',
                'message' => 'renamed',
            ],
        ];
    }

    /**
     * Download selected file
     *
     * @param $disk
     * @param $path
     *
     * @return mixed
     */
    public function download($disk, $path)
    {
        // if file name not in ASCII format
        if (!preg_match('/^[\x20-\x7e]*$/', basename($path))) {
            $filename = Str::ascii(basename($path));
        } else {
            $filename = basename($path);
        }

        return Storage::disk($disk)->download($path, $filename);
    }

    /**
     * Create thumbnails
     *
     * @param $disk
     * @param $path
     *
     * @return \Illuminate\Http\Response|mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function thumbnails($disk, $path)
    {
        // create thumbnail
        if ($this->configRepository->getCache()) {
            $thumbnail = Image::cache(function ($image) use ($disk, $path) {
                $image->make(Storage::disk($disk)->get($path))->fit(80);
            }, $this->configRepository->getCache());

            // output
            return response()->make(
                $thumbnail,
                200,
                ['Content-Type' => Storage::disk($disk)->mimeType($path)]
            );
        }

        $thumbnail = Image::make(Storage::disk($disk)->get($path))->fit(80);

        return $thumbnail->response();
    }

    /**
     * Image preview
     *
     * @param $disk
     * @param $path
     *
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function preview($disk, $path)
    {
        // get image
        $preview = Image::make(Storage::disk($disk)->get($path));

        return $preview->response();
    }

    /**
     * Get file URL
     *
     * @param $disk
     * @param $path
     *
     * @return array
     */
    public function url($disk, $path)
    {
        return [
            'result' => [
                'status'  => 'success',
                'message' => null,
            ],
            'url'    => Storage::disk($disk)->url($path),
        ];
    }

    /**
     * Create new directory
     *
     * @param $disk
     * @param $path
     * @param $name
     *
     * @return array
     */
    public function createDirectory($disk, $path, $name)
    {
        // path for new directory
        $directoryName = $this->newPath($path, $name);

        // check - exist directory or no
        if (Storage::disk($disk)->exists($directoryName)) {
            return [
                'result' => [
                    'status'  => 'warning',
                    'message' => 'dirExist',
                ],
            ];
        }

        // create new directory
        Storage::disk($disk)->makeDirectory($directoryName);

        // get directory properties
        $directoryProperties = $this->directoryProperties(
            $disk,
            $directoryName
        );

        // add directory properties for the tree module
        $tree = $directoryProperties;
        $tree['props'] = ['hasSubdirectories' => false];

        return [
            'result'    => [
                'status'  => 'success',
                'message' => 'dirCreated',
            ],
            'directory' => $directoryProperties,
            'tree'      => [$tree],
        ];
    }

    /**
     * Create new file
     *
     * @param $disk
     * @param $path
     * @param $name
     *
     * @return array
     */
    public function createFile($disk, $path, $name)
    {
        // path for new file
        $path = $this->newPath($path, $name);

        // check - exist file or no
        if (Storage::disk($disk)->exists($path)) {
            return [
                'result' => [
                    'status'  => 'warning',
                    'message' => 'fileExist',
                ],
            ];
        }

        // create new file
        Storage::disk($disk)->put($path, '');

        // get file properties
        $fileProperties = $this->fileProperties($disk, $path);

        return [
            'result' => [
                'status'  => 'success',
                'message' => 'fileCreated',
            ],
            'file'   => $fileProperties,
        ];
    }

    /**
     * Update file
     *
     * @param $disk
     * @param $path
     * @param $file
     *
     * @return array
     */
    public function updateFile($disk, $path, $file)
    {
        // update file
        Storage::disk($disk)->putFileAs(
            $path,
            $file,
            $file->getClientOriginalName()
        );

        // path for new file
        $filePath = $this->newPath($path, $file->getClientOriginalName());

        // get file properties
        $fileProperties = $this->fileProperties($disk, $filePath);

        return [
            'result' => [
                'status'  => 'success',
                'message' => 'fileUpdated',
            ],
            'file'   => $fileProperties,
        ];
    }

    /**
     * Stream file - for audio and video
     *
     * @param $disk
     * @param $path
     *
     * @return mixed
     */
    public function streamFile($disk, $path)
    {
        // if file name not in ASCII format
        if (!preg_match('/^[\x20-\x7e]*$/', basename($path))) {
            $filename = Str::ascii(basename($path));
        } else {
            $filename = basename($path);
        }

        return Storage::disk($disk)
            ->response($path, $filename, ['Accept-Ranges' => 'bytes']);
    }
}
