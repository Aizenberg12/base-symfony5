<?php


namespace App\Controller\File;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\File;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelInterface;


class FileService
{

    private $em;
    private $kernel;

    public function __construct(EntityManagerInterface $em, KernelInterface $kernel)
    {
        $this->em = $em;
        $this->kernel = $kernel;
    }

    public function uploadFile(UploadedFile $uploadedFile): File
    {
        switch ($uploadedFile->getError()) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new HttpException(413, 'file.errors.too_big');
            case UPLOAD_ERR_PARTIAL:
                throw new HttpException(400, 'file.errors.partial');
            case UPLOAD_ERR_NO_FILE:
                throw new HttpException(400, 'file.errors.no_file');
            default:
                throw new HttpException(500, 'file.errors.unknown_error');
        }

        $ext = $uploadedFile->getClientOriginalExtension();

        $name = self::setName($ext);
        $path = self::setPath($name);

        $uploadedFile->move($this->setDir(), $name);

        return $this->createFile($name, $path);
    }

    public function setDir(): string
    {
        $dir = $this->kernel->getProjectDir() . '/public/uploads/';

        $dir .= self::getCurrentDate();

        if (!file_exists($dir) && !mkdir($dir, 0755) && !is_dir($dir)) {
            throw new \http\Exception\RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        return $dir;
    }

    private static function getCurrentDate(): string
    {
        $currentDate = new DateTime();
        return $currentDate->format('Y-m-d');
    }

    public function createFile($name, $path): File
    {
        $file = new File();

        $file->name = $name;
        $file->path = $path;

        $this->em->persist($file);
        $this->em->flush();

        return $file;
    }

    private static function setPath($name): string
    {
        return self::getCurrentDate() . '/' . $name;
    }

    private static function setName($ext = 'jpg'): string
    {
        return md5(uniqid('', false)) . '.' . $ext;
    }

    public function deleteFile(File $file): void
    {
        $dir = $this->kernel->getProjectDir() . '/public/uploads/';
        $path = $dir . $file->path;

        if (file_exists($path)) {
            unlink($path);
        }

        $this->em->remove($file);
        $this->em->flush();
    }

}