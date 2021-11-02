<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\File\CreateFile;
use App\Controller\File\RemoveFile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Webant\BaseBundle\Entity\BaseEntity;

/**
 * Class File
 * @ORM\Entity
 * @package App\Entity
 * @ApiResource(
 *     description = "File",
 *     collectionOperations={
 *          "get",
 *          "createFile"={
 *              "method"="POST",
 *              "controller"=CreateFile::class,
 *              "defaults"={"_api_receive"=false},
 *              "swagger_context"={
 *                       "consumes"={
 *                           "multipart/form-data",
 *                       },
 *                      "responses"={
 *                         "201"={"description"="ok"},
 *                         "404"={"description"="Not Found"},
 *                         "400"={"description"="Bad Request"},
 *                      },
 *                      "parameters"={
 *                          {"name"="file", "in"="formData", "type"="file"},
 *                      },
 *             },
 *          }
 *     },
 *     itemOperations={
 *          "get",
 *          "delete"={
 *              "method"="DELETE",
 *              "controller"=RemoveFile::class,
 *          },
 *     },
    *     attributes={
    *         "normalization_context"={"groups"={"GetFile", "GetObjBase"}},
    *         "denormalization_context"={"groups"={"SetFile"}}
    *     }
 * )
 * @UniqueEntity("name")
 */
class File extends BaseEntity
{
    /**
     * @var string $name
     * @ORM\Column(type="string", nullable=false)
     * @Groups({"GetFile", "GetObjFile", "SetFile"})
     */
    public $name;

    /**
     * @var string $path
     * @ORM\Column(type="string", nullable=false)
     * @Groups({"GetFile", "GetObjFile", "SetFile"})
     */
    public $path;
}