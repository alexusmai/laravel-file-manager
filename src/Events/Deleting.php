<?php

namespace Alexusmai\LaravelFileManager\Events;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class Deleting
{
    /**
     * @var string
     */
    private $disk;

    /**
     * @var array
     */
    private $items;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->disk  = $request->input('disk');
        $this->items = $request->input('items');
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
    public function items()
    {
        return array_map(function ($item) {
            $item['size'] = Storage::disk($this->disk())->size($item['path']);

            return $item;
        }, $this->items);
    }
}
