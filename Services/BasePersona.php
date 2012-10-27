<?php

/*
 * This file is part of the BGPersonaBundle package.
 *
 * (c) bitgrave <http://bitgrave.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BG\PersonaBundle\Services;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;


class BasePersona
{
    protected $assertion;
    protected $session;
    protected $verifier_url;
    protected $audience_url;

    public function __construct($verifier_url = null, $audience_url = null, $session)
    {
        $this->verifier_url = $verifier_url;
        $this->audience_url = $audience_url;
        $this->session = $session;
        $this->assertion = array();
    }

    public function getAccessToken($user = null)
    {
        $this->assertion['audience'] = urlencode('dev.newsmarq.l');
        if (isset($_POST['assertion'])&&($_POST['assertion'])) $this->assertion['assertion'] = $_POST['assertion'];

        $verifier_token = json_decode($this->__verifierPost('https://verifier.login.persona.org/verify', $this->assertion)); // 'https://browserid.org/verify'
        if ($verifier_token && $verifier_token->status === 'okay')
        {
            // temporary save the access token values ...
            $this->session->set('persona_email', $verifier_token->email);
            $this->session->set('persona_expires', $verifier_token->expires);
            $this->session->set('persona_status', $verifier_token->status);

            // toke verfied and valid (not expired)
            return $verifier_token;
        }

        /* Return null for failure */
        return null;
    }

    function __verifierPost($url, $data)
    {
        $fields_string = http_build_query($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        $err = curl_errno($ch);
        if ($err > 0)
            return -1;
        curl_close($ch);
        return $result;
    }
}
