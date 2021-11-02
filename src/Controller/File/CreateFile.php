<?php


namespace App\Controller\File;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CreateFile
{

    private $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function __invoke(Request $request)
    {
        $uploadedImage = $request->files->get('file');

        if (empty($uploadedImage)) {
            throw new BadRequestHttpException('Файл не загружен.');
        }

        return $this->fileService->uploadFile($uploadedImage);
    }
}