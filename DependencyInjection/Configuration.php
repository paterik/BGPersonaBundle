<?php

/*
 * This file is part of the BGPersonaBundle package.
 *
 * (c) paterik <http://github.com/paterik>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BG\PersonaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
    Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bg_persona');

        $rootNode
            ->fixXmlConfig('permission', 'permissions')
            ->children()
                ->scalarNode('verifier_url')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('audience_url')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('logging')->defaultValue('%kernel.debug%')->end()
                ->scalarNode('culture')->defaultValue('de_DE')->end()
                ->arrayNode('class')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('service')->defaultValue('BG\PersonaBundle\Persona\PersonaService')->end()
                        ->scalarNode('helper')->defaultValue('BG\PersonaBundle\Templating\Helper\PersonaHelper')->end()
                        ->scalarNode('twig')->defaultValue('BG\PersonaBundle\Twig\Extension\PersonaExtension')->end()
                    ->end()
                ->end()
                ->arrayNode('permissions')->prototype('scalar')->end()
            ->end();

        return $treeBuilder;
    }
}
