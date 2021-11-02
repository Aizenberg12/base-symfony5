<?php
/**
 * Created by PhpStorm.
 * User: kwant
 * Date: 21.08.18
 * Time: 15:06
 */

namespace App\OAuth2;

use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use OAuth2\IOAuth2Storage;
use OAuth2\OAuth2 as BaseOAuth;
use OAuth2\Model\IOAuth2Client;
use OAuth2\IOAuth2GrantUser;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\Response;


class OAuth2 extends BaseOAuth
{

    /**
     * The provided authorization grant is invalid, expired,
     * revoked, does not match the redirection URI used in the
     * authorization request, or was issued to another client.
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-5.2
     */
    public const WARNING_USE_TWO_FACTOR = 'two_factor_required';

    protected $authCodeLifetime = 3600;

    protected $em;

    protected $mailer;
    private $OAuthStorage;

    public function __construct(IOAuth2Storage $storage,
        $config = [],
        EntityManagerInterface $em,
        OAuthStorage $OAuthStorage)
    {
        parent::__construct($storage, $config);
        if (isset($config['auth_code_life'])) {
            $this->authCodeLifetime = $config['auth_code_life'];
        }
        $this->em = $em;
        $this->OAuthStorage = $OAuthStorage;
    }


    /**
     * @param IOAuth2Client $client
     * @param array $input
     * @return array|bool
     * @throws OAuth2ServerException
     */
    protected function grantAccessTokenUserCredentials(IOAuth2Client $client, array $input)
    {
        if (!($this->storage instanceof IOAuth2GrantUser)) {
            throw new OAuth2ServerException(Response::HTTP_BAD_REQUEST, self::ERROR_UNSUPPORTED_GRANT_TYPE);
        }

        if (!$input['username'] || !$input['password']) {
            throw new OAuth2ServerException(Response::HTTP_BAD_REQUEST,
                self::ERROR_INVALID_REQUEST,
                'Missing parameters. "username" and "password" required');
        }

        $stored = $this->OAuthStorage->checkUserCredentials($client, $input['username'], $input['password']);
        if ($stored === false) {
            throw new OAuth2ServerException(Response::HTTP_BAD_REQUEST,
                self::ERROR_INVALID_GRANT,
                'Invalid username and password combination');
        }

        /** @var User $user */
        $user = $stored['data'];
        if (!$user->isEnabled()) {
            throw new OAuth2ServerException(Response::HTTP_BAD_REQUEST,
                self::ERROR_INVALID_GRANT,
                'User is disabled');
        }

        return $stored;
    }

}