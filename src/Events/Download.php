<?php

namespace Alexusmai\LaravelFileManager\Events;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class Download
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
     * Download constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->disk = $request->input('disk');
        $this->path = $request->input('path');
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
     * @return int|null
     */
    public function size(): ?int
    {
        return Storage::disk($this->disk())->size($this->path());
    }
}
