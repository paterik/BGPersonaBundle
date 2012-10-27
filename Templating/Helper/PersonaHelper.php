<?php

/*
 * This file is part of the BGPersonaBundle package.
 *
 * (c) bitgrave <http://bitgrave.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BG\PersonaBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Templating\EngineInterface;

class PersonaHelper extends Helper
{
    protected $templating;
    protected $logging;
    protected $culture;
    protected $scope;

    public function __construct(EngineInterface $templating, $logging = true, $culture = 'de_DE', array $scope = array())
    {
        $this->templating  = $templating;
        $this->logging     = $logging;
        $this->culture     = $culture;
        $this->scope       = $scope;
    }

    /**
     * setup the initializing params for persona auth implementation
     *
     * @param array  $parameters An array of parameters for the initialization template
     * @param string $name       A template name
     *
     * @return string An HTML string
     */
    public function initialize($parameters = array(), $name = null)
    {
        $name = $name ?: 'BGPersonaBundle::initialize.html.twig';
        return $this->templating->render($name, $parameters + array(
        ));
    }

    /**
     * setup the login/logoutButton params for persona auth implementation
     *
     * @param array  $parameters An array of parameters for the initialization template
     * @param string $name       A template name
     *
     * @return string An HTML string
     */
    public function loginButton($parameters = array(), $name = null)
    {

        $name = $name ?: 'BGPersonaBundle::userButton.html.twig';
        return $this->templating->render($name, $parameters + array(
        ));
    }

    /**
     *
     * render the logout url for persona auth
     *
     * @param array $parameters
     * @param null $name
     * @return mixed
     */
    public function logoutUrl($parameters = array(), $name = null)
    {
        return '<a href="#fake-logout">logout</a>';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return 'persona';
    }
}
