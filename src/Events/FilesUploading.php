<?php

namespace Alexusmai\LaravelFileManager\Events;

use Alexusmai\LaravelFileManager\Services\ConfigService\ConfigRepository;
use Illuminate\Http\Request;

class FilesUploading
{
    /**
     * @var string
     */
    private $disk;

    /**
     * @var string
     */
    private $path;

    /**
     * @var \Illuminate\Http\UploadedFile
     */
    private $files;

    /**
     * @var string|null
     */
    private $overwrite;

    /**
     * FilesUploading constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request, ConfigRepository $configRepository)
    {
        $this->disk = $request->input('disk');
        $this->path = $request->input('path');
        $this->files = $request->file('files');
        $this->overwrite = $request->input('overwrite');
        $this->configRepository = $configRepository;
    }

    /**
     * @return string
     */
    public function disk()
    {
        return $this->disk;
    }

    /**
     * @return string
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function files()
    {
        return array_map(function ($file) {

            if ($this->configRepository->filenameSlugable()) {
                $filename = \Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            } else {
                $filename = $file->getClientOriginalName();
            }

            return [
                'name' => $file->getClientOriginalName(),
                'path' => $this->path . '/' . $file->getClientOriginalName(),
                'extension' => $file->extension(),
            ];
        }, $this->files);
    }

    /**
     * @return bool
     */
    public function overwrite()
    {
        return !!$this->overwrite;
    }
}
