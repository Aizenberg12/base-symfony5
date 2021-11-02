<?php


namespace App\Services\Filesystem;


use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\FileExistsException;
use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3Service
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    /**
     * @param $fileDir
     * @param UploadedFile|null $file
     * @return bool
     * @throws FileExistsException
     */
    public function uploadFileToStorage($fileDir, UploadedFile $file = null): bool
    {


        $fullFileDir = "";
        if (!$file) {
            $rootDir = $this->container->get('kernel')->getRootDir();
            $fullFileDir = $rootDir . '/../public/uploads/' . $fileDir;

            $fileContent = file_get_contents($fullFileDir);
        } else {
            $fileContent = file_get_contents($file->getPathname());
        }


        $key = $this->container->getParameter('s3.key');
        $secret = $this->container->getParameter('s3.secret');
        $endpoint = $this->container->getParameter('s3.endpoint');
        $region = $this->container->getParameter('s3.region');
        $bucketName = $this->container->getParameter('s3.bucket.name');

        $client = new S3Client([
            'credentials' => [
                'key' => $key,
                'secret' => $secret,
            ],
            'region' => $region,
            'version' => 'latest',
            'endpoint' => $endpoint
        ]);

        $adapter = new AwsS3Adapter($client, $bucketName);

        $filesystem = new Filesystem($adapter);

        $filesystem->write($fileDir, $fileContent, ['visibility' => 'public']);

        if (!$file) {
            @unlink($fullFileDir);
        }

        return $fileDir;
    }
}