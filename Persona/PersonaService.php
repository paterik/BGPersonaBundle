<?php

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