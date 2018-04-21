<?php

namespace Alexusmai\LaravelFileManager\Controllers;

use Alexusmai\LaravelFileManager\Services\FileManagerService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileManagerController extends Controller
{
    /**
     * @var FileManagerService
     */
    public $service;

    /**
     * FileManagerController constructor.
     * @param FileManagerService $service
     */
    public function __construct(FileManagerService $service)
    {
        $this->service = $service;
    }

    /**
     * Initialize file manager settings
     * @return \Illuminate\Http\JsonResponse
     */
    public function initialize()
    {
        return response()->json(
            $this->service->initialize()
        );
    }

    /**
     * Get files and directories for the selected path and disk
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function content(Request $request)
    {
        return response()->json(
            $this->service->content(
                $request->input('disk'),
                $request->input('path')
            )
        );
    }

    /**
     * Directory tree
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function tree(Request $request)
    {
        return response()->json(
            $this->service->tree(
                $request->input('disk'),
                $request->input('path')
            )
        );
    }

    /**
     * Check the selected disk
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectDisk(Request $request)
    {
        return response()->json(
            $this->service->selectDisk(
                $request->input('disk')
            )
        );
    }

    /**
     * Create new directory
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createDirectory(Request $request)
    {
        return response()->json(
            $this->service->createDirectory(
                $request->input('disk'),
                $request->input('path'),
                $request->input('name')
            )
        );
    }

    /**
     * Upload files
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        return response()->json(
            $this->service->upload(
                $request->input('disk'),
                $request->input('path'),
                $request->file('files'),
                $request->input('overwrite')
            )
        );
    }

    /**
     * Delete files and folders
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        return response()->json(
            $this->service->delete(
                $request->input('disk'),
                $request->input('items')
            )
        );
    }

    /**
     * Copy / Cut files and folders
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paste(Request $request)
    {
        return response()->json(
            $this->service->paste(
                $request->input('disk'),
                $request->input('path'),
                $request->input('clipboard')
            )
        );

    }

    /**
     * Rename item
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rename(Request $request)
    {
        return response()->json(
            $this->service->rename(
                $request->input('disk'),
                $request->input('newName'),
                $request->input('oldName')
            )
        );
    }

    /**
     * Download file
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(Request $request)
    {
        return $this->service->download(
            $request->input('disk'),
            $request->input('path')
        );
    }

    /**
     * Create thumbnails
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function thumbnails(Request $request)
    {
        return $this->service->thumbnails(
            $request->input('disk'),
            $request->input('path')
        );
    }

    /**
     * Image preview
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function preview(Request $request)
    {
        return $this->service->preview(
            $request->input('disk'),
            $request->input('path')
        );
    }

    /**
     * File url
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function url(Request $request)
    {
        return response()->json(
            $this->service->url(
                $request->input('disk'),
                $request->input('path')
            )
        );
    }

    /**
     * Integration with ckeditor 4
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ckeditor(Request $request)
    {
        return view('file-manager::ckeditor');
    }
}