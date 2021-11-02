<?php


namespace App\Services\User;


use ApiPlatform\Core\Validator\ValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webant\UserBundle\Entity\UserInterface;
use Webant\UserBundle\Mailer\MailerInterface;
use Webant\UserBundle\Model\UserManagerInterface;

class RegistrationService
{
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var MailerInterface
     */
    private $mailer;
    /**
     * @var UserManagerInterface
     */
    private $userManager;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        ValidatorInterface $validator,
        MailerInterface $mailer,
        UserManagerInterface $userManager,
        ContainerInterface $container
    ) {
        $this->validator = $validator;
        $this->mailer = $mailer;
        $this->userManager = $userManager;
        $this->container = $container;
    }


    public function createUser(UserInterface $user, bool $sendMail = true): void
    {

        if (!empty($user->getUsername() && empty($user->getEmail()))) {
            $user->setEmail($user->getUsername() . '@science-fest.ru');
        }

        if (empty($user->getUsername() && !empty($user->getEmail()))) {
            $user->setUsername($user->getEmail());
        }

        $this->validator->validate($user);

        if ($sendMail && $this->container->getParameter('webant_user.email.enabled')) {
            $this->mailer->sendConfirmationEmailMessage($user);
        }


        $this->userManager->updateUser($user, true);
    }
}