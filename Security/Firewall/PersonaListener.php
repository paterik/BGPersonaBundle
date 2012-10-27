<?php

/*
 * This file is part of the BGPersonaBundle package.
 *
 * (c) bitgrave <http://bitgrave.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BG\PersonaBundle\Security\Firewall;

use BG\PersonaBundle\Security\Authentication\Token\PersonaUserToken;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\HttpFoundation\Request;

/**
 * Facebook authentication listener.
 */
class PersonaListener extends AbstractAuthenticationListener
{

    protected function attemptAuthentication(Request $request)
    {
        return $this->authenticationManager->authenticate(new PersonaUserToken());
    }

    /*public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $token = new PersonaUserToken();
    }*/

}
