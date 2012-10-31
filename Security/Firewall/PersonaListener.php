<?php

/*
 * This file is part of the BGPersonaBundle package.
 *
 * (c) paterik <http://github.com/paterik>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BG\PersonaBundle\Security\Firewall;

use BG\PersonaBundle\Security\Authentication\Token\PersonaUserToken;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\HttpFoundation\Request;

/**
 * persona authentication listener.
 */
class PersonaListener extends AbstractAuthenticationListener
{

    protected function attemptAuthentication(Request $request)
    {
        return $this->authenticationManager->authenticate(new PersonaUserToken());
    }

}
