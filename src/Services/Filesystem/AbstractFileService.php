<?php


namespace App\Services\Filesystem;


use App\Entity\File;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class AbstractFileService
{
    protected $em;

    public const BLACK_LIST_EXTENSIONS = [
        'php',
        'sh',
    ];

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param iterable $files
     * @throws HttpException
     */
    public function validateFiles(iterable $files): void
    {
        /** @var  UploadedFile $uploadedFile */
        foreach ($files as $uploadedFile) {
            switch ($uploadedFile->getError()) {
                case UPLOAD_ERR_OK:
                    continue 2;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new HttpException(413, 'errors.uploads.too_big');
                case UPLOAD_ERR_PARTIAL:
                    throw new HttpException(400, 'errors.uploads.partial');
                case UPLOAD_ERR_NO_FILE:
                    throw new HttpException(400, 'errors.uploads.no_file');
                default:
                    throw new HttpException(500, 'errors.uploads.unknown_error');
            }
        }
    }

    /**
     * @param Request $request
     * @param array $params
     * @return File[]|array
     * @throws Exception
     */
    public function uploadFilesFromRequest(Request $request, array $params = []): array
    {
        /** @var $files File[] */
        $files = [];
        /** @var UploadedFile $uploadedFile */
        foreach ($request->files as $uploadedFile) {
            $files[] = $this->uploadFile($uploadedFile, $params);
        }

        return $files;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param array $params
     * @return File
     * @throws Exception
     */
    public function uploadFile(UploadedFile $uploadedFile, array $params = []): File
    {
        $path = $this->generateFilePath($uploadedFile);
        $fileObject = $this->doSaveFile($uploadedFile, $path, $params);
        static::setFileDimensions($uploadedFile, $fileObject);

        return $fileObject;
    }

    public static function setFileDimensions(UploadedFile $uploadedFile, File $fileObject): void
    {
        $path = $uploadedFile->getRealPath();
        $mime = $uploadedFile->getMimeType();
        $fileObject->type = $mime;
        if (!is_string($mime) || strpos($mime, 'image') !== 0) {
            return;
        }
        [$fileObject->width, $fileObject->height] = getimagesize($path);
    }


    /**
     * generate file path(subdirectory + name + ext)
     * @param UploadedFile $file
     * @return string
     * @throws Exception
     */
    protected function generateFilePath(UploadedFile $file): string
    {
        // generateDirName: TODO: make it configurable
        // TODO: make generateDirPath separate from generateFileName
        $currentDate = new DateTime();
        $dir = $currentDate->format('Y-m-d');
        $basePath = $this->generateRandomString(16);

        $ext = $file->getClientOriginalExtension();
        if (empty($ext)) {
            $ext = $file->guessClientExtension();
        }
        if (in_array($ext, static::BLACK_LIST_EXTENSIONS, true)) {
            $ext = null;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $basePath;

        if ($ext) {
            $path .= ".$ext";
        }
        return $path;
    }

    /**
     * @param UploadedFile $file
     * @param $path
     * @param array $params
     * @return File
     */
    abstract protected function doSaveFile(UploadedFile $file, $path, array $params = []);

    /**
     * @from https://stackoverflow.com/a/31107425
     * Generate a random string, using a cryptographically secure
     * pseudorandom number generator (random_int)
     * For PHP 7, random_int is a PHP core function
     * For PHP 5.x, depends on https://github.com/paragonie/random_compat
     * @param int $length How many characters do we want?
     * @param string $keyspace A string of all possible characters
     *                         to select from
     * @return string
     * @throws Exception
     */
    protected function generateRandomString(int $length,
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
    {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces [] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
}
