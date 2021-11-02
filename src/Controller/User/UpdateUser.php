<?php


namespace App\Controller\User;


use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\User\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Security;
use Webant\UserBundle\Model\UserManagerInterface;

class UpdateUser
{
    private $security;
    private $userManager;
    private $validator;


    public function __construct(Security $security, UserManagerInterface $userManager, ValidatorInterface $validator)
    {
        $this->security = $security;
        $this->userManager = $userManager;
        $this->validator = $validator;
    }


    public function __invoke(User $data)
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user->hasRole('ROLE_ADMIN') && $user->getId() !== $data->getId()) {
            throw new AccessDeniedHttpException();
        }

        if ($data->getPlainPassword()) {
            if (!$user->hasRole('ROLE_ADMIN')) {
                throw new AccessDeniedHttpException();
            }
            $this->validator->validate($data);
            $this->userManager->updatePassword($data);
            $this->userManager->flushUser($data);
        }

        return $data;
    }
}