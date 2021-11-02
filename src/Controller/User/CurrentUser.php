<?php


namespace App\Controller\User;


use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CurrentUser
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function __invoke(): UserInterface
    {
        return $this->security->getUser();
    }

}