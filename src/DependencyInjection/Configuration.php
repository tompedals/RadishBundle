<?php

namespace Radish\RadishBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('radish');

        $rootNode
            ->children()
                ->arrayNode('connection')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('host')->defaultValue('localhost')->end()
                        ->scalarNode('port')->defaultValue(5672)->end()
                        ->scalarNode('login')->defaultValue('guest')->end()
                        ->scalarNode('password')->defaultValue('guest')->end()
                        ->scalarNode('vhost')->defaultValue('/')->end()
                        ->scalarNode('read_timeout')->end()
                        ->scalarNode('write_timeout')->end()
                        ->scalarNode('connect_timeout')->end()
                        ->scalarNode('channel_max')->end()
                        ->scalarNode('frame_max')->end()
                        ->scalarNode('heartbeat')->end()
                        ->scalarNode('cacert')->end()
                        ->scalarNode('cert')->end()
                        ->scalarNode('key')->end()
                        ->scalarNode('verify')->end()
                    ->end()
                ->end()
                ->arrayNode('exchanges')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->booleanNode('durable')->defaultTrue()->end()
                            ->scalarNode('type')
                                ->validate()
                                ->ifNotInArray(array('direct', 'topic', 'fanout'))
                                    ->thenInvalid('Invalid exchange type "%s"')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('queues')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->booleanNode('durable')->defaultTrue()->end()
                            ->scalarNode('exchange')->isRequired()->end()
                            ->scalarNode('dead_letter_exchange')->end()
                            ->scalarNode('max_priority')->end()
                            ->scalarNode('routing_key')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('producers')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('exchange')->end()
                        ->end()
                    ->end()
                ->end()
                ->append($this->consumerNode('consumers'))
                ->append($this->consumerNode('pollers'))
            ->end();

        return $treeBuilder;
    }

    private function consumerNode($type)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($type);

        $node->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->validate()
                    ->ifTrue(function ($v) {
                        return isset($v['queues']) && !empty($v['queues']) && isset($v['queue']) && !empty($v['queue']);
                    })
                    ->thenInvalid('A consumer configuration can contain either "queue" or "queues", but not both.')
                ->end()
                ->children()
                    ->scalarNode('queue')->end()
                    ->scalarNode('worker')->end()
                    ->arrayNode('queues')
                        ->useAttributeAsKey('queue')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('queue')->end()
                                ->scalarNode('worker')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('middleware')
                        ->prototype('variable')->end()
                    ->end()
                    ->scalarNode('interval')->end()
            ->end()
        ->end();

        return $node;
    }
}
