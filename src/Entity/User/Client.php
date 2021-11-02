<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 19.12.18
 * Time: 12:06
 */

namespace App\Entity\User;

use ApiPlatform\Core\Annotation\ApiResource;
use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * Class Client
 * @ORM\Entity
 * @ApiResource(attributes={
 *     "normalization_context"={"groups"={"GetClient"}},
 *     "denormalization_context"={"groups"={"SetClient"}}
 * })
 */
class Client extends BaseClient
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"GetClient"})
     */
    protected $id;

    /**
     * @var string
     * @Groups({"GetClient"})
     */
    protected $randomId;

    /**
     * @var string
     * @Groups({"GetClient"})
     */
    protected $secret;

    /**
     * @var string
     * @Groups({"GetClient","SetClient"})
     */
    protected $allowedGrantTypes;

}
