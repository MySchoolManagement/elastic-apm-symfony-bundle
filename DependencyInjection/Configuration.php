<?php

declare(strict_types=1);

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElasticApmBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('elastic_apm');
        if (\method_exists(TreeBuilder::class, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $rootNode = $treeBuilder->root('elastic_apm');
        }

        $rootNode
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('interactor')->end()
                ->booleanNode('logging')
                    ->info('Write logs to a PSR3 logger whenever we send data to Elastic APM.')
                    ->defaultFalse()
                ->end()
                ->arrayNode('exceptions')
                    ->canBeDisabled()
                    ->children()
                        ->arrayNode('ignored_exceptions')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('deprecations')
                    ->canBeDisabled()
                ->end()
                ->arrayNode('warnings')
                    ->canBeDisabled()
                ->end()
                ->arrayNode('custom_labels')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('custom_context')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('http')
                    ->canBeDisabled()
                    ->children()
                        ->scalarNode('transaction_naming')
                            ->defaultValue('route')
                            ->validate()
                                ->ifNotInArray(['uri', 'route', 'controller', 'service'])
                                ->thenInvalid('Invalid transaction naming scheme "%s", must be "uri", "route", "controller" or "service".')
                            ->end()
                        ->end()
                        ->scalarNode('transaction_naming_service')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('commands')
                    ->canBeDisabled()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
