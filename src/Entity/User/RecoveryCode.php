<?php


namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Webant\BaseBundle\Entity\BaseEntity;

/**
 * @ORM\Entity()
 */
class RecoveryCode extends BaseEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="User",inversedBy="recoveryCode",cascade={"remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    public $user;
    /**
     * @ORM\Column(name="code",type="string")
     */
    public $code;
}
