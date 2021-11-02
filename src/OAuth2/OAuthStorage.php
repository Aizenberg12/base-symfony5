<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 18.01.19
 * Time: 0:33
 */

namespace App\OAuth2;

use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use FOS\OAuthServerBundle\Model\AccessTokenManagerInterface;
use FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\OAuthServerBundle\Model\RefreshTokenManagerInterface;
use FOS\OAuthServerBundle\Storage\OAuthStorage as BaseOAuthStorage;
use InvalidArgumentException;
use OAuth2\Model\IOAuth2Client;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthStorage extends BaseOAuthStorage
{
    private $em;

    public function __construct(ClientManagerInterface $clientManager,
        AccessTokenManagerInterface $accessTokenManager,
        RefreshTokenManagerInterface $refreshTokenManager,
        AuthCodeManagerInterface $authCodeManager,
        ?UserProviderInterface $userProvider = null,
        ?EncoderFactoryInterface $encoderFactory = null,
        EntityManagerInterface $em)
    {
        parent::__construct($clientManager,
            $accessTokenManager,
            $refreshTokenManager,
            $authCodeManager,
            $userProvider,
            $encoderFactory);
        $this->em = $em;
    }

    public function checkUserCredentials(IOAuth2Client $client, $username, $password)
    {
        if (!$client instanceof ClientInterface) {
            throw new InvalidArgumentException('Client has to implement the ClientInterface');
        }

        try {
            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $username]);
            if (!$user || !$user->hasRole(User::ROLE_DEFAULT)) {
                $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
                if (!$user || !$user->hasRole('ROLE_ADMIN')) {
                    throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
                }
            }
        } catch (AuthenticationException $e) {
            return false;
        }

        $encoder = $this->encoderFactory->getEncoder($user);
        if ($encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
            return [
                'data' => $user,
            ];
        }

        return false;
    }

}