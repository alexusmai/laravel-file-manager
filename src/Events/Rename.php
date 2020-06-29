<?php

namespace Alexusmai\LaravelFileManager\Events;

use Illuminate\Http\Request;
use Storage;

class Rename
{
    /**
     * @var string
     */
    private $disk;

    /**
     * @var string
     */
    private $newName;

    /**
     * @var string
     */
    private $oldName;

    /**
     * @var string
     */
    private $type;

    /**
     * Rename constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->disk = $request->input('disk');
        $this->newName = $request->input('newName');
        $this->oldName = $request->input('oldName');

        $info = Storage::disk($request->input('disk'))
            ->getMetadata($request->input('oldName'));

        $this->type = $info['type'];
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
    public function newName()
    {
        return $this->newName;
    }

    /**
     * @return string
     */
    public function oldName()
    {
        return $this->oldName;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->type;
    }
}
