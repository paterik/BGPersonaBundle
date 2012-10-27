<?php

/*
 * This file is part of the BGPersonaBundle package.
 *
 * (c) bitgrave <http://bitgrave.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BG\PersonaBundle\Twig\Extension;

use BG\PersonaBundle\Templating\Helper\PersonaHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PersonaExtension extends \Twig_Extension
{


    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        return array(
            'persona_initialize' => new \Twig_Function_Method($this, 'renderInitialize', array('is_safe' => array('html'))),
            'persona_login_button' => new \Twig_Function_Method($this, 'renderLoginButton', array('is_safe' => array('html'))),
            'persona_logout_url' => new \Twig_Function_Method($this, 'renderLogoutUrl', array('is_safe' => array('html'))),
        );
    }

    /**
     * @see PersonaHelper::initialize()
     */
    public function renderInitialize($parameters = array(), $name = null)
    {
        return $this->container->get('bg_persona.helper')->initialize($parameters, $name ?: 'BGPersonaBundle::initialize.html.twig');
    }

    /**
     * @see PersonaHelper::loginButton()
     */
    public function renderLoginButton($parameters = array(), $name = null)
    {
        return $this->container->get('bg_persona.helper')->loginButton($parameters, $name ?: 'BGPersonaBundle::userButton.html.twig');
    }


    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'persona';
    }
}