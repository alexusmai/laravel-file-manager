<?php

namespace Alexusmai\LaravelFileManager\Events;

use Illuminate\Http\Request;

class Zipping
{
    /**
     * @var string
     */
    private $disk;

    /**
     * @var array
     */
    private $elements;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $path;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->disk     = $request->input('disk');
        $this->elements = $request->input('elements');
        $this->name     = $request->input('name');
        $this->path     = $request->input('path');
    }

    /**
     * @return string
     */
    public function disk()
    {
        return $this->disk;
    }

    /**
     * @return array
     */
    public function elements(): array
    {
        return $this->elements;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function path()
    {
        return $this->path;
    }
}
