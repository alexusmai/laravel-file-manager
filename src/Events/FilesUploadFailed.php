<?php

namespace Alexusmai\LaravelFileManager\Events;

use Illuminate\Http\Request;

class FilesUploadFailed
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

    private string $reason;

    /**
     * FilesUploaded constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request,string $reason)
    {
        $this->disk = $request->input('disk');
        $this->path = $request->input('path');
        $this->files = $request->file('files');
        $this->overwrite = $request->input('overwrite');
        $this->reason = $reason;
    }

    /**
     * @return string
     */
    public function disk()
    {
        return $this->disk;
    }
    public function reason()
    {
        return $this->reason;
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
            return [
                'name'      => $file->getClientOriginalName(),
                'path'      => $this->path.'/'.$file->getClientOriginalName(),
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
