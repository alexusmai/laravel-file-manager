<?php

namespace Alexusmai\LaravelFileManager\Events;

class DiskSelected
{
    /**
     * @var string
     */
    private $disk;

    /**
     * @param $disk
     */
    public function __construct($disk)
    {
        $this->disk = $disk;
    }

    /**
     * @return string
     */
    public function disk()
    {
        return $this->disk;
    }
}
