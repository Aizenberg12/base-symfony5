<?php


namespace App\Services\Filesystem;


use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class FlyFileService extends AbstractFileService
{
    protected $adapter;
    protected $em;
    protected $fs;

    public const BLACK_LIST_EXTENSIONS = [
        'php',
        'sh',
    ];

    public function __construct(EntityManagerInterface $em, AdapterInterface $adapter = null)
    {
        if (is_null($adapter)) {
            $adapter = new NullAdapter();
        }

        $this->adapter = $adapter;
        $this->fs = new Filesystem($adapter);
        parent::__construct($em);
    }

    /**
     * @param UploadedFile $file
     * @param $path
     * @param array $params
     * @return File
     * @throws FileExistsException
     */
    protected function doSaveFile(UploadedFile $file, $path, array $params = [])
    {
        $objFile = new File();

        $objFile->name = $file->getClientOriginalName();
        $objFile->path = $path;

        $stream = fopen($file->getRealPath(), 'r+');
        $this->fs->writeStream($path, $stream);
        fclose($stream);

        return $objFile;
    }

}
