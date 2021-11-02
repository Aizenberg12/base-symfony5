<?php

namespace App\Entity\User;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\User\CurrentUser;
use App\Controller\User\RegistrationUser;
use App\Controller\User\UpdateUser;
use App\Controller\User\RemoveUser;
use App\Entity\Event\UserEvent;
use App\Entity\File;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Webant\UserBundle\Entity\User as BaseUser;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ApiResource(
 *    description = "User",
 *     collectionOperations={
 *     "get"={
 *          "normalization_context"={"groups"={"CurrentUser", "GetBase", "GetFile", "GetObjUser"}}
 *     },
 *     "post"={
 *           "method"="POST",
 *           "controller"=RegistrationUser::class,
 *           "denormalization_context"={"groups"={"SetRegisterUser"}, "swagger_definition_name": "RegisterUser"},
 *     },
 *     "current"={
 *         "method": "GET",
 *         "path": "/users/current",
 *         "controller": CurrentUser::class,
 *         "pagination_enabled": false,
 *         "normalization_context"={"groups"={"CurrentUser", "GetObjUser", "GetObjFile"}},
 *         "swagger_context": {
 *             "parameters": {},
 *         }
 *     },
 *     },
 *     itemOperations={
 *     "get"={
 *           "normalization_context"={"groups"={"GetUser"}, "swagger_definition_name": "GetUser"},
 *     },
 *       "delete"={
 *           "method"="DELETE",
 *           "path"="/users/{id}",
 *           "controller"=RemoveUser::class,
 *       },
 *       "update"={
 *           "method"="PUT",
 *           "path"="/users/{id}",
 *           "controller"=UpdateUser::class,
 *           "denormalization_context"={"groups"={"UpdateUser"}, "swagger_definition_name": "UpdateUser"}
 *       }
 *     },
 *     attributes={
 *         "normalization_context"={"groups"={"CurrentUser", "GetFile", "GetObjUser"}},
 *         "denormalization_context"={"groups"={"SetRegisterUser"}}
 *     }
 * )
 * @UniqueEntity("phone")
 */
class User extends BaseUser
{

    /**
     * @ORM\Column(unique=true)
     * @Groups({"GetUser", "SetUser", "SetEmail", "SetRegisterUser", "CurrentUser", "GetEmail", "GetObjUser", "UpdateUser"})
     * @Assert\NotBlank()
     */
    protected $email;

    /**
     * @ORM\Column()
     * @Groups({"GetUser","GetObjUser", "SetUser", "SetRegisterUser", "UpdateUser", "CurrentUser"})
     * @Assert\NotBlank()
     */
    public $fullName;

    /**
     * @Assert\Regex("/^[\d\W]+$/")
     * @ORM\Column(unique=true)
     * @Groups({"GetUser","SetUser", "SetRegisterUser", "UpdateUser", "CurrentUser"})
     * @Assert\NotBlank()
     */
    public $phone;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     * @var string
     * @Assert\Length(
     *     min=8,
     *     max=4096,
     *     minMessage="webant_user.password.short",
     *     maxMessage="webant_user.password.long"
     * )
     * @Groups({"SetUser", "SetRegisterUser", "UpdateUser"})
     */
    protected $plainPassword;

    /**
     * @var string
     * @Groups({"SetPassword"})
     */
    public $oldPassword;

    /**
     * @var string
     * @Groups({"SetPassword"})
     * @Assert\Length(min="5",minMessage="min 5")
     */
    public $newPassword;

    /**
     * @ORM\Column(type="array")
     * @Groups({"GetUser", "GetObjUser"})
     */
    protected $roles;

    /**
     * @ORM\OneToMany(targetEntity="RecoveryCode",cascade={"remove"},mappedBy="user")
     */
    private $recoveryCode;


    /**
     * @var string
     * @Groups({"GetUser", "SetCode"})
     */
    public $code;

    /**
     * @ORM\Column(type="datetime", name="date_create", nullable=false)
     * @Groups({"GetUser", "GetObjUser"})
     */
    public $dateCreate;

    public function __construct()
    {
        parent::__construct();
        $this->dateCreate = new DateTime();
        $this->userEvents = new ArrayCollection();
    }

    public function setRolesRaw(array $roles): User
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->roles[] = strtoupper($role);
        }
        $this->roles = array_unique($this->roles);
        $this->roles = array_values($this->roles);

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email ?: '';
    }

}
