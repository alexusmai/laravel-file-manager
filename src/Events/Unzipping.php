<?php

namespace Alexusmai\LaravelFileManager\Events;

use Illuminate\Http\Request;

class Unzipping
{
    /**
     * @var string
     */
    private $disk;

    /**
     * @var string|null
     */
    private $folder;

    /**
     * @var string
     */
    private $path;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->disk   = $request->input('disk');
        $this->folder = $request->input('folder');
        $this->path   = $request->input('path');
    }

    /**
     * @return string
     */
    public function disk()
    {
        return $this->disk;
    }

    /**
     * @return string|null
     */
    public function folder()
    {
        return $this->folder;
    }

    /**
     * @return string
     */
    public function path()
    {
        return $this->path;
    }
}
