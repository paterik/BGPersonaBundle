<?php

/*
 * This file is part of the BGPersonaBundle package.
 *
 * (c) bitgrave <http://bitgrave.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BG\PersonaBundle;

use BG\PersonaBundle\DependencyInjection\Security\Factory\PersonaFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BGPersonaBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new PersonaFactory());
    }
}
