<?php

namespace Alexusmai\LaravelFileManager\Middleware;

use Alexusmai\LaravelFileManager\Services\ACLService\ACL;
use Closure;

class FileManagerACL
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // if ACL is OFF
        if (!config('file-manager.acl')) {
            return $next($request);
        }

        // get disk and path name
        $disk = $request->has('disk') ? $request->input('disk') : null;
        $path = $request->has('path') ? $request->input('path') : '/';

        if (!$disk) {
            return $next($request);
        }

        // get ACL service
        $acl = resolve(ACL::class);

        // switch by route name
        switch ($request->route()->getName()) {
            // read ============================================================
            case 'fm.tree':
            case 'fm.content':
            case 'fm.preview':
            case 'fm.thumbnails':
            case 'fm.url':
            case 'fm.stream-file':
                // need r access
                if ($acl->getAccessLevel($disk, $path) === 0) {
                    return $this->errorMessage();
                }

                break;

            // download ========================================================
            case 'fm.download':
                // need r access
                abort_if($acl->getAccessLevel($disk, $path) === 0, 403);

                break;

            // Create new file or directory ====================================
            case 'fm.create-file':
            case 'fm.create-directory':
                // get new file or folder name
                $name = $request->has('name') ? $request->input('name') : null;

                // need r/w access
                if ($acl->getAccessLevel($disk, $path.'/'.$name) !== 2) {
                    return $this->errorMessage();
                }

                break;

            // update file =====================================================
            case 'fm.update-file':
                // get updated file name
                $name = $request->has('file') ? $request->input('file') : null;

                // need r/w access
                if ($acl->getAccessLevel($disk, $path.'/'.$name) !== 2) {
                    return $this->errorMessage();
                }

                break;

            // upload files ====================================================
            case 'fm.upload':
                // need r/w access
                if ($acl->getAccessLevel($disk, $path.'/*') !== 2) {
                    return $this->errorMessage();
                }

                break;

            // delete ==========================================================
            case 'fm.delete':
                // get items to delete
                $items = $request->has('items') ? $request->input('items') : [];
                // filter
                $firstFall = array_first($items,
                    function ($value) use ($disk, $acl) {
                        // need r/w access
                        return $acl->getAccessLevel($disk, $value['path'])
                            !== 2;
                    }, null);

                // if founded one access error
                if ($firstFall) {
                    return $this->errorMessage();
                }

                break;

            // paste ===========================================================
            case 'fm.paste':
                // can user write to selected folder?
                $writeToFolder = $acl->getAccessLevel($disk, $path);
                // need r/w access
                if ($writeToFolder !== 2) {
                    return $this->errorMessage();
                }

                // get clipboard data
                $clipboard = $request->input('clipboard');

                if ($clipboard['type'] === 'copy') {
                    // can user read selected files and folders?
                    $checkDirs = array_first($clipboard['directories'],
                        function ($value) use ($clipboard, $acl) {
                            // need r access
                            return $acl->getAccessLevel($clipboard['disk'],
                                    $value) === 0;
                        }, null);


                    $checkFiles = array_first($clipboard['files'],
                        function ($value) use ($clipboard, $acl) {
                            // need r access
                            return $acl->getAccessLevel($clipboard['disk'],
                                    $value) === 0;
                        }, null);
                } else {
                    // can user delete selected files and folders?
                    $checkDirs = array_first($clipboard['directories'],
                        function ($value) use ($clipboard, $acl) {
                            // need r/w access
                            return $acl->getAccessLevel($clipboard['disk'],
                                    $value) !== 2;
                        }, null);


                    $checkFiles = array_first($clipboard['files'],
                        function ($value) use ($clipboard, $acl) {
                            // need r/w access
                            return $acl->getAccessLevel($clipboard['disk'],
                                    $value) !== 2;
                        }, null);
                }

                if ($checkDirs || $checkFiles) {
                    return $this->errorMessage();
                }

                break;

            // rename ==========================================================
            case 'fm.rename':
                // old name
                $path = $request->has('oldName') ? $request->input('oldName')
                    : null;

                // need r/w access
                if ($acl->getAccessLevel($disk, $path) !== 2) {
                    return $this->errorMessage();
                }

                break;

            // zip =============================================================
            case 'fm.zip':
                // can user write to selected folder?
                $writeToFolder = $acl->getAccessLevel($disk, $path);
                // need r/w access
                if ($writeToFolder !== 2) {
                    return $this->errorMessage();
                }

                // data to zip
                $elements = $request->input('elements');

                // can user read selected files and folders?
                $checkDirs = array_first($elements['directories'],
                    function ($value) use ($disk, $elements, $acl) {
                        // need r access
                        return $acl->getAccessLevel($disk, $value) === 0;
                    }, null);


                $checkFiles = array_first($elements['files'],
                    function ($value) use ($disk, $elements, $acl) {
                        // need r access
                        return $acl->getAccessLevel($disk, $value) === 0;
                    }, null);

                if ($checkDirs || $checkFiles) {
                    return $this->errorMessage();
                }

                break;

            // unzip ===========================================================
            case 'fm.unzip':
                // can user write to selected folder?
                $writeToFolder = $acl->getAccessLevel($disk, dirname($path));
                // need r/w access
                if ($writeToFolder !== 2) {
                    return $this->errorMessage();
                }

                // can user read zip file?
                $readZip = $acl->getAccessLevel($disk, $path);
                // need r access
                if ($readZip === 0) {
                    return $this->errorMessage();
                }

                break;
        }

        // return request
        return $next($request);
    }

    /**
     * ACL Error message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorMessage()
    {
        return response()->json([
            'result' => [
                'status'  => 'error',
                'message' => trans('file-manager::response.aclError'),
            ],
        ]);
    }
}
