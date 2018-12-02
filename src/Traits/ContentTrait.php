<?php

namespace Alexusmai\LaravelFileManager\Traits;

use Illuminate\Support\Collection;
use Storage;

trait ContentTrait
{

    /**
     * Get content for the selected disk and path
     *
     * @param      $disk
     * @param null $path
     *
     * @return array
     */
    public function getContent($disk, $path = null)
    {
        $content = Storage::disk($disk)->listContents($path);

        // get a list of directories
        $directories = $this->filterDir($content);

        // get a list of files
        $files = $this->filterFile($content);

        return compact('directories', 'files');
    }

    /**
     * Get directories with properties
     *
     * @param      $disk
     * @param null $path
     *
     * @return array
     */
    public function directoriesWithProperties($disk, $path = null)
    {
        $content = Storage::disk($disk)->listContents($path);

        return $this->filterDir($content);
    }

    /**
     * Get files with properties
     *
     * @param      $disk
     * @param null $path
     *
     * @return array
     */
    public function filesWithProperties($disk, $path = null)
    {
        $content = Storage::disk($disk)->listContents($path);

        return $this->filterFile($content);
    }

    /**
     * Get directories for tree module
     *
     * @param $disk
     * @param $path
     *
     * @return array
     */
    public function getDirectoriesTree($disk, $path = null)
    {
        $directories = $this->directoriesWithProperties($disk, $path);

        foreach ($directories as $index => $dir) {
            $directories[$index]['props'] = [
                'hasSubdirectories' => Storage::disk($disk)
                    ->directories($dir['path']) ? true : false,
            ];
        }

        return $directories;
    }

    /**
     * File properties
     *
     * @param      $disk
     * @param null $path
     *
     * @return mixed
     */
    public function fileProperties($disk, $path = null)
    {
        $file = Storage::disk($disk)->getMetadata($path);

        $pathInfo = pathinfo($path);

        $file['basename'] = $pathInfo['basename'];
        $file['dirname'] = $pathInfo['dirname'] === '.' ? ''
            : $pathInfo['dirname'];
        $file['extension'] = isset($pathInfo['extension'])
            ? $pathInfo['extension'] : '';
        $file['filename'] = $pathInfo['filename'];

        return $file;
    }

    /**
     * Get properties for the selected directory
     *
     * @param      $disk
     * @param null $path
     *
     * @return mixed
     */
    public function directoryProperties($disk, $path = null)
    {
        $directory = Storage::disk($disk)->getMetadata($path);

        $pathInfo = pathinfo($path);

        $directory['basename'] = $pathInfo['basename'];
        $directory['dirname'] = $pathInfo['dirname'] === '.' ? ''
            : $pathInfo['dirname'];

        return $directory;
    }

    /**
     * Get only directories
     *
     * @param $content
     *
     * @return array
     */
    protected function filterDir($content)
    {
        return Collection::make($content)
            ->where('type', 'dir')
            ->map(function ($item, $key) {
                return array_except($item, ['filename']);
            })
            ->values()
            ->all();
    }

    /**
     * Get only files
     *
     * @param $content
     *
     * @return array
     */
    protected function filterFile($content)
    {
        return Collection::make($content)
            ->where('type', 'file')
            ->values()
            ->all();
    }
}
