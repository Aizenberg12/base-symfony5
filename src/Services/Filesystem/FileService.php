<?php


namespace App\Services\Filesystem;


use App\Entity\File;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileService extends AbstractFileService
{
    protected $uploadsDir;
    protected $em;

    public const BLACK_LIST_EXTENSIONS = [
        'php',
        'sh',
    ];

    public function setUploadsDir($dir): void
    {
        $this->uploadsDir = $dir;
    }

    /**
     * TODO: make it configurable
     * TODO: sanitize path here
     * !!CAUTION: this func doesn't sanitize path
     * !! IF YOU PASS path=../../../../some/critical/dir it can try to save file where it shouldn't
     * @param UploadedFile $file
     * @param $path
     * @param array $params
     * @return File
     * @throws Exception
     */
    protected function doSaveFile(UploadedFile $file, $path, array $params = [])
    {
        $objFile = new File();

        $objFile->name = $file->getClientOriginalName();
        $objFile->path = $path;

        $pathInfo = pathinfo($path);
        $fullDir = $this->uploadsDir . DIRECTORY_SEPARATOR . $pathInfo['dirname'];

        $file->move($fullDir, $pathInfo['basename']);
        return $objFile;
    }

}
