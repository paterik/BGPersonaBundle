<?php

/*
 * This file is part of the BGPersonaBundle package.
 *
 * (c) paterik <http://github.com/paterik>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BG\PersonaBundle\Security\Authentication\Provider;

use BG\PersonaBundle\Security\User\UserManagerInterface;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;

use BG\PersonaBundle\Security\Authentication\Token\PersonaUserToken;
use BG\PersonaBundle\Services\BasePersona;

class PersonaProvider implements AuthenticationProviderInterface
{
    private $persona;
    private $userProvider;
    private $userChecker;
    private $createUserIfNotExists;

    public function __construct($providerKey, BasePersona $persona, UserProviderInterface $userProvider = null, UserCheckerInterface $userChecker = null, $createUserIfNotExists = false )
    {
        if (null !== $userProvider && null === $userChecker) {
            throw new \InvalidArgumentException('$userChecker cannot be null, if $userProvider is not null.');
        }

        if ($createUserIfNotExists && !$userProvider instanceof UserManagerInterface) {
            throw new \InvalidArgumentException('The $userProvider must implement UserManagerInterface if $createIfNotExists is true.');
        }

        $this->persona = $persona;
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
        $this->createUserIfNotExists = $createUserIfNotExists;
    }

    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        $user = $token->getUser();

        if ($user instanceof UserInterface) {
            $newToken = new PersonaUserToken($user, $user->getRoles());
            $newToken->setAttributes($token->getAttributes());

            return $newToken;
        }

        try {

            if ($accessToken = $this->persona->getAccessToken($user))
            {
                $newToken = $this->createAuthenticatedToken($accessToken);
                $newToken->setAttributes($token->getAttributes());

                return $newToken;
            }

        } catch (AuthenticationException $failed) {
          throw $failed;
        } catch (\Exception $failed) {
          throw new AuthenticationException($failed->getMessage(), null, $failed->getCode(), $failed);
        }

        // our user not able to verfiy, store, refresh, whatever handler
        throw new AuthenticationException('The persona user could not be retrieved from the session.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof PersonaUserToken;
    }

    protected function createAuthenticatedToken($uid)
    {
        if (null === $this->userProvider) {
            return new PersonaUserToken($uid, array('ROLE_PERSONA_USER'));
        }

        try
        {
            $user = $this->userProvider->loadUserByUsername($uid->email);
            $this->userChecker->checkPostAuth($user);
        }   catch (UsernameNotFoundException $ex) {
            if (!$this->createUserIfNotExists) {throw $ex;}
            $user = $this->userProvider->createUserFromAccessToken($uid);
        }

        if (!$user instanceof UserInterface) {
            throw new \RuntimeException('User provider did not return an implementation of user interface.');
        }

        $bla5=$user->getRoles();

        return new PersonaUserToken($user, $user->getRoles());
    }
}
