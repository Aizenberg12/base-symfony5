<?php


namespace App\Controller\File;


use App\Entity\File;
use Symfony\Component\HttpFoundation\JsonResponse;

class RemoveFile
{
    private $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function __invoke(File $data)
    {
        $this->fileService->deleteFile($data);

        return new JsonResponse('Файл удалён.', JsonResponse::HTTP_NO_CONTENT);
    }

}