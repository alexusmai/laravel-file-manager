<?php

namespace Alexusmai\LaravelFileManager\Traits;

use Illuminate\Support\Collection;
use Storage;

trait ContentTrait {

    /**
     * Get content for the selected disk and path
     * @param $disk
     * @param $path
     * @return array
     */
    public function getContent($disk, $path = null)
    {
        $contents = Storage::disk($disk)->listContents($path);

        // get a list of directories
        $directories = $this->filterDir($contents);

        // get a list of files
        $files = $this->filterFile($contents);

        return compact('directories', 'files');
    }

    /**
     * Get directories with properties
     * @param $disk
     * @param null $path
     * @return array
     */
    public function directoriesWithProperties($disk, $path = null)
    {
        $contents = Storage::disk($disk)->listContents($path);

        return $this->filterDir($contents);
    }

    /**
     * Get files with properties
     * @param $disk
     * @param null $path
     * @return array
     */
    public function filesWithProperties($disk, $path = null)
    {
        $contents = Storage::disk($disk)->listContents($path);

        return $this->filterFile($contents);
    }

    /**
     * Get directories for tree module
     * @param $disk
     * @param $path
     * @return array
     */
    public function getDirectoriesTree($disk, $path = null)
    {
        $directories = $this->directoriesWithProperties($disk, $path);

        foreach ($directories as $index => $dir){

            $directories[$index]['props'] = ['hasSubdirectories' => Storage::disk($disk)->directories($dir['path']) ? true : false];
        }

        return $directories;
    }

    /**
     * Get properties for the selected directory
     * @param $disk
     * @param null $path
     * @return array|false
     */
    public function directoryProperties($disk, $path = null)
    {
        $directory = Storage::disk($disk)->getMetadata($path);

        $pathInfo = pathinfo($path);

        $directory['basename'] = $pathInfo['basename'];
        $directory['dirname'] = $pathInfo['dirname'] === '.' ? '' : $pathInfo['dirname'];

        return $directory;
    }

    /**
     * Get only directories
     * @param $contents
     * @return array
     */
    protected function filterDir($contents)
    {
        return Collection::make($contents)
            ->where('type', 'dir')
            ->map(function ($item, $key) {
                return array_except($item, ['filename']);
            })
            ->values()
            ->all();
    }

    /**
     * Get only files
     * @param $contents
     * @return array
     */
    protected function filterFile($contents)
    {
        return Collection::make($contents)
            ->where('type', 'file')
            ->values()
            ->all();
    }
}