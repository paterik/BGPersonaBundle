<?php

/*
 * This file is part of the BGPersonaBundle package.
 *
 * (c) paterik <http://github.com/paterik>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BG\PersonaBundle\Persona;

use Symfony\Component\HttpFoundation\Session\Session;
use BG\PersonaBundle\Services\BasePersona;

class PersonaService extends BasePersona
{
    protected $session;
    public function __construct(Session $session, $verifier_url = null, $audience_url = null)
    {
        $this->session = $session;
        parent::__construct($session, $verifier_url, $audience_url);
    }
}