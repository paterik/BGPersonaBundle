<?php

/*
 * This file is part of the BGPersonaBundle package.
 *
 * (c) bitgrave <http://bitgrave.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BG\PersonaBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class PersonaUserToken extends AbstractToken
{
    // uid = userobject, we can fetch all persona_* properties from this object directly, so kill all $persona_ protperties b4 release
    public function __construct($uid = '', array $roles = array())
    {
        parent::__construct($roles);

        $this->setUser($uid);
        if (!empty($uid)) {
            $this->setAuthenticated(true);
        }
    }

    public function getCredentials()
    {
        return '';
    }
}