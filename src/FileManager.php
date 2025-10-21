<?php

namespace Alexusmai\LaravelFileManager;

use Alexusmai\LaravelFileManager\Events\Deleted;
use Alexusmai\LaravelFileManager\Services\ConfigService\ConfigRepository;
use Alexusmai\LaravelFileManager\Services\TransferService\TransferFactory;
use Alexusmai\LaravelFileManager\Traits\CheckTrait;
use Alexusmai\LaravelFileManager\Traits\ContentTrait;
use Alexusmai\LaravelFileManager\Traits\PathTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use League\Flysystem\FilesystemException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileManager
{
    use PathTrait, ContentTrait, CheckTrait;

    /**
     * @var ConfigRepository
     */
    public ConfigRepository $configRepository;

    /**
     * FileManager constructor.
     *
     * @param ConfigRepository $configRepository
     */
    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    /**
     * Initialize App
     *
     * @return array
     */
    public function initialize(): array
    {
        if (!config()->has('file-manager')) {
            return [
                'result' => [
                    'status'  => 'danger',
                    'message' => 'noConfig',
                ],
            ];
        }

        $config = [
            'acl'           => $this->configRepository->getAcl(),
            'leftDisk'      => $this->configRepository->getLeftDisk(),
            'rightDisk'     => $this->configRepository->getRightDisk(),
            'leftPath'      => $this->configRepository->getLeftPath(),
            'rightPath'     => $this->configRepository->getRightPath(),
            'windowsConfig' => $this->configRepository->getWindowsConfig(),
            'hiddenFiles'   => $this->configRepository->getHiddenFiles(),
        ];

        // disk list
        foreach ($this->configRepository->getDiskList() as $disk) {
            if (array_key_exists($disk, config('filesystems.disks'))) {
                $config['disks'][$disk] = Arr::only(
                    config('filesystems.disks')[$disk], ['driver']
                );
            }
        }

        // get language
        $config['lang'] = app()->getLocale();

        return [
            'result' => [
                'status'  => 'success',
                'message' => null,
            ],
            'config' => $config,
        ];
    }

    /**
     * Get files and directories for the selected path and disk
     *
     * @param $disk
     * @param $path
     *
     * @return array
     * @throws FilesystemException
     */
    public function content($disk, $path): array
    {
        $content = $this->getContent($disk, $path);

        return [
            'result'      => [
                'status'  => 'success',
                'message' => null,
            ],
            'directories' => $content['directories'],
            'files'       => $content['files'],
        ];
    }

    /**
     * Get part of the directory tree
     *
     * @param $disk
     * @param $path
     *
     * @return array
     * @throws FilesystemException
     */
    public function tree($disk, $path): array
    {
        $directories = $this->getDirectoriesTree($disk, $path);

        return [
            'result'      => [
                'status'  => 'success',
                'message' => null,
            ],
            'directories' => $directories,
        ];
    }

    /**
     * Upload files with strict validation:
     * - Enforce allowFileTypes
     * - Block disguised PHP payload unless PHP extensions explicitly allowed
     * - Validate size, MIME, and magic bytes for common types
     *
     * @param string|null $disk
     * @param string|null $path
     * @param array|null  $files
     * @param bool        $overwrite
     * @return array
     */
    public function upload($disk, $path, $files, $overwrite): array
    {
        $fileNotUploaded = false;

        $allowed = $this->configRepository->getAllowFileTypes();
        $allowed = is_array($allowed) ? array_map('strtolower', $allowed) : [];
        $phpAllowed = in_array('php', $allowed, true) || in_array('phtml', $allowed, true) || in_array('pht', $allowed, true) || in_array('phar', $allowed, true);

        foreach ((array) $files as $file) {
            if (!$file || !$file->isValid()) {
                $fileNotUploaded = true;
                continue;
            }

            if ($this->configRepository->getMaxUploadFileSize() && $file->getSize() / 1024 > $this->configRepository->getMaxUploadFileSize()) {
                $fileNotUploaded = true;
                continue;
            }

            $ext = strtolower($file->getClientOriginalExtension());

            if (!empty($allowed) && !in_array($ext, $allowed, true)) {
                $fileNotUploaded = true;
                continue;
            }

            $realPath = $file->getRealPath();
            if (!$realPath || !is_file($realPath)) {
                $fileNotUploaded = true;
                continue;
            }

            if (!$phpAllowed) {
                $mime = $this->detectMime($realPath);
                if ($this->looksLikePhpMime($mime)) {
                    $fileNotUploaded = true;
                    continue;
                }
                if ($this->containsPhpPayload($realPath)) {
                    $fileNotUploaded = true;
                    continue;
                }
            }

            if (!$this->validateByExtension($realPath, $ext)) {
                $fileNotUploaded = true;
                continue;
            }

            $name = $file->getClientOriginalName();
            if ($this->configRepository->getSlugifyNames()) {
                $name = Str::slug(
                        Str::replace(
                            '.' . $file->getClientOriginalExtension(),
                            '',
                            $name
                        )
                    ) . '.' . $ext;
            }

            if (!$overwrite && Storage::disk($disk)->exists($path . '/' . $name)) {
                continue;
            }

            Storage::disk($disk)->putFileAs(
                $path,
                $file,
                $name
            );
        }

        if ($fileNotUploaded) {
            return [
                'result' => [
                    'status'  => 'warning',
                    'message' => 'notAllUploaded',
                ],
            ];
        }

        return [
            'result' => [
                'status'  => 'success',
                'message' => 'uploaded',
            ],
        ];
    }


    /**
     * Delete files and folders
     *
     * @param $disk
     * @param $items
     *
     * @return array
     */
    public function delete($disk, $items): array
    {
        $deletedItems = [];

        foreach ($items as $item) {
            if (!Storage::disk($disk)->exists($item['path'])) {
                continue;
            } else {
                if ($item['type'] === 'dir') {
                    Storage::disk($disk)->deleteDirectory($item['path']);
                } else {
                    Storage::disk($disk)->delete($item['path']);
                }
            }

            $deletedItems[] = $item;
        }

        event(new Deleted($disk, $deletedItems));

        return [
            'result' => [
                'status'  => 'success',
                'message' => 'deleted',
            ],
        ];
    }

    /**
     * Copy / Cut - Files and Directories
     *
     * @param $disk
     * @param $path
     * @param $clipboard
     *
     * @return array
     */
    public function paste($disk, $path, $clipboard): array
    {
        // compare disk names
        if ($disk !== $clipboard['disk']) {

            if (!$this->checkDisk($clipboard['disk'])) {
                return $this->notFoundMessage();
            }
        }

        $transferService = TransferFactory::build($disk, $path, $clipboard);

        return $transferService->filesTransfer();
    }

    /**
     * Rename file or folder
     *
     * @param $disk
     * @param $newName
     * @param $oldName
     *
     * @return array
     */
    public function rename($disk, $newName, $oldName)
	{
		$allowed = $this->configRepository->getAllowFileTypes();
		$allowed = is_array($allowed) ? array_map('strtolower', $allowed) : [];

		$oldBase = basename(str_replace('\\','/',$oldName));
		$oldDir = trim(str_replace('\\','/',dirname($oldName)),'/');
		$newBase = basename(str_replace('\\','/',$newName));

		$oldExt = strtolower(pathinfo($oldBase, PATHINFO_EXTENSION));
		$newExt = strtolower(pathinfo($newBase, PATHINFO_EXTENSION));

		if ($this->hasInvalidBaseName($newBase)) {
			return ['result'=>['status'=>'error','message'=>'invalid_name']];
		}

		if ($oldExt !== $newExt) {
			if (!empty($allowed)) {
				if (!in_array($newExt, $allowed, true)) {
					return ['result'=>['status'=>'error','message'=>'extension_not_allowed']];
				}
			} else {
				return ['result'=>['status'=>'error','message'=>'changing_extension_forbidden']];
			}
		}

		$oldPath = ltrim($oldName,'/');
		$newPath = ($oldDir ? $oldDir.'/' : '').$newBase;

		Storage::disk($disk)->move($oldPath, $newPath);
		return ['result'=>['status'=>'success','message'=>'renamed']];
	}

    /**
     * Download selected file
     *
     * @param $disk
     * @param $path
     *
     * @return StreamedResponse
     */
    public function download($disk, $path): StreamedResponse
    {
        // if file name not in ASCII format
        if (!preg_match('/^[\x20-\x7e]*$/', basename($path))) {
            $filename = Str::ascii(basename($path));
        } else {
            $filename = basename($path);
        }

        return Storage::disk($disk)->download($path, $filename);
    }

    /**
     * Create thumbnails
     *
     * @param $disk
     * @param $path
     *
     * @return Response|mixed
     * @throws BindingResolutionException
     */
    public function thumbnails($disk, $path): mixed
    {
        return response()->make(
            Image::read(
                Storage::disk($disk)->get($path))
                ->coverDown(80, 80)
                ->encode(),
            200,
            ['Content-Type' => Storage::disk($disk)->mimeType($path)]
        );
    }

    /**
     * Image preview
     *
     * @param $disk
     * @param $path
     *
     * @return mixed
     * @throws BindingResolutionException
     */
    public function preview($disk, $path): mixed
    {
        return response()->make(
            Image::read(Storage::disk($disk)->get($path))->encode(),
            200,
            ['Content-Type' => Storage::disk($disk)->mimeType($path)]
        );
    }

    /**
     * Get file URL
     *
     * @param $disk
     * @param $path
     *
     * @return array
     */
    public function url($disk, $path): array
    {
        return [
            'result' => [
                'status'  => 'success',
                'message' => null,
            ],
            'url'    => Storage::disk($disk)->url($path),
        ];
    }

    /**
     * Create new directory
     *
     * @param $disk
     * @param $path
     * @param $name
     *
     * @return array
     */
    public function createDirectory($disk, $path, $name)
    {
        $directoryName = $this->newPath($path, $name);

        if (Storage::disk($disk)->exists($directoryName)) {
            return [
                'result' => [
                    'status'  => 'warning',
                    'message' => 'dirExist',
                ],
            ];
        }

        Storage::disk($disk)->makeDirectory($directoryName);
        $directoryProperties = $this->directoryProperties(
            $disk,
            $directoryName
        );

        // add directory properties for the tree module
        $tree          = $directoryProperties;
        $tree['props'] = ['hasSubdirectories' => false];

        return [
            'result'    => [
                'status'  => 'success',
                'message' => 'dirCreated',
            ],
            'directory' => $directoryProperties,
            'tree'      => [$tree],
        ];
    }

    /**
     * Create new file
     *
     * @param $disk
     * @param $path
     * @param $name
     *
     * @return array
     */
    public function createFile($disk, $path, $name): array
    {
        $path = $this->newPath($path, $name);

        if (Storage::disk($disk)->exists($path)) {
            return [
                'result' => [
                    'status'  => 'warning',
                    'message' => 'fileExist',
                ],
            ];
        }

        Storage::disk($disk)->put($path, '');
        $fileProperties = $this->fileProperties($disk, $path);

        return [
            'result' => [
                'status'  => 'success',
                'message' => 'fileCreated',
            ],
            'file'   => $fileProperties,
        ];
    }
	
	/**
     * Has Invalid Base Name
     *
     * @param $name
     *
     * @return false
     */
	private function hasInvalidBaseName($name)
	{
		if ($name === '' || trim($name) === '') return true;
		if (preg_match('/\.\./', $name)) return true;
		if (preg_match('/\.$/', $name)) return true;
		if (substr_count($name, '.') > 1) return true;
		if (strpos($name, '/') !== false || strpos($name, '\\') !== false) return true;
		if (!preg_match('/^[^\.]+\.[A-Za-z0-9]+$/', $name)) return true;
		return false;
	}

    /**
     * Update file
     *
     * @param $disk
     * @param $path
     * @param $file
     *
     * @return array
     */
    public function updateFile($disk, $path, $file): array
    {
        Storage::disk($disk)->putFileAs(
            $path,
            $file,
            $file->getClientOriginalName()
        );

        $filePath       = $this->newPath($path, $file->getClientOriginalName());
        $fileProperties = $this->fileProperties($disk, $filePath);

        return [
            'result' => [
                'status'  => 'success',
                'message' => 'fileUpdated',
            ],
            'file'   => $fileProperties,
        ];
    }

    /**
     * Stream file - for audio and video
     *
     * @param $disk
     * @param $path
     *
     * @return StreamedResponse
     */
    public function streamFile($disk, $path): StreamedResponse
    {
        // if file name not in ASCII format
        if (!preg_match('/^[\x20-\x7e]*$/', basename($path))) {
            $filename = Str::ascii(basename($path));
        } else {
            $filename = basename($path);
        }

        return Storage::disk($disk)->response($path, $filename, ['Accept-Ranges' => 'bytes']);
    }
	
	    /**
     * Detect MIME type via finfo
     *
     * @param string $path
     * @return string
     */
    private function detectMime(string $path): string
    {
        $f = finfo_open(FILEINFO_MIME_TYPE);
        $m = $f ? finfo_file($f, $path) : '';
        if ($f) finfo_close($f);
        return strtolower($m ?: '');
    }

    /**
     * Heuristic check for PHP-like MIME types
     *
     * @param string $mime
     * @return bool
     */
    private function looksLikePhpMime(string $mime): bool
    {
        $phpMimes = ['text/x-php','application/x-php','application/php','application/x-httpd-php'];
        foreach ($phpMimes as $pm) {
            if (stripos($mime, $pm) !== false) return true;
        }
        return false;
    }

    /**
     * Scan file content for PHP tags in non-PHP uploads
     *
     * @param string $path
     * @return bool
     */
    private function containsPhpPayload(string $path): bool
    {
        $h = @fopen($path,'rb');
        if (!$h) return false;
        $limit = 8388608;
        $buf = '';
        while (!feof($h) && $limit > 0) {
            $chunk = fread($h, min(131072, $limit));
            if ($chunk === false) break;
            $buf .= $chunk;
            if (preg_match('/<\?(php|=)/i', $buf)) {
                fclose($h);
                return true;
            }
            if (strlen($buf) > 32) $buf = substr($buf, -32);
            $limit -= strlen($chunk);
        }
        fclose($h);
        return false;
    }

    /**
     * Validate magic bytes by extension (images/pdf/zip/rar/text-like)
     *
     * @param string $path
     * @param string $ext
     * @return bool
     */
    private function validateByExtension(string $path, string $ext): bool
    {
        $ext = strtolower($ext);

        if (in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) {
            if (function_exists('exif_imagetype')) {
                $t = @exif_imagetype($path);
                if (($ext==='jpg' || $ext==='jpeg') && $t === IMAGETYPE_JPEG) return true;
                if ($ext==='png' && $t === IMAGETYPE_PNG) return true;
                if ($ext==='gif' && $t === IMAGETYPE_GIF) return true;
                if ($ext==='webp' && defined('IMAGETYPE_WEBP') && $t === IMAGETYPE_WEBP) return true;
                return false;
            }
            $h = @fopen($path,'rb'); if(!$h) return false;
            $head = fread($h, 12); fclose($h);
            $hex = bin2hex($head);
            if (($ext==='jpg'||$ext==='jpeg') && substr($hex,0,4)==='ffd8') return true;
            if ($ext==='png' && substr($hex,0,8)==='89504e47') return true;
            if ($ext==='gif' && substr($hex,0,6)==='474946') return true;
            if ($ext==='webp' && substr($hex,0,8)==='52494646') return true;
            return false;
        }

        if ($ext==='pdf') {
            $h = @fopen($path,'rb'); if(!$h) return false;
            $head = fread($h, 5); fclose($h);
            if ($head !== '%PDF-') return false;
            return true;
        }

        if ($ext==='zip') {
            $h = @fopen($path,'rb'); if(!$h) return false;
            $head = fread($h, 4); fclose($h);
            $hex = bin2hex($head);
            return in_array($hex, ['504b0304','504b0506','504b0708'], true);
        }

        if ($ext==='rar') {
            $h = @fopen($path,'rb'); if(!$h) return false;
            $head = fread($h, 8);
            $more = fread($h, 2);
            fclose($h);
            $hex = bin2hex($head.$more);
            if (strpos($hex, '526172211a0700') === 0) return true;
            if (strpos($hex, '526172211a070100') === 0) return true;
            return false;
        }

        if (in_array($ext, ['txt','csv'], true)) {
            $mime = $this->detectMime($path);
            if ($this->looksLikePhpMime($mime)) return false;
            return (stripos($mime, 'text/') === 0) || $mime === 'application/octet-stream';
        }

        return true;
    }
}
