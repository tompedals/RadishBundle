<?php

namespace Radish\RadishBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RadishExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $this->loadConnection($config['connection'], $container);

        $container->getDefinition('radish.broker.exchange_registry')->setArguments([
            new Reference('radish.broker.connection'),
            $config['exchanges']
        ]);

        $container->getDefinition('radish.broker.queue_registry')->setArguments([
            new Reference('radish.broker.connection'),
            $config['queues']
        ]);

        $container->setParameter('radish.consumers', $config['consumers']);

        foreach ($config['consumers'] as $name => $consumer) {
            $this->loadConsumer($name, $consumer, $container);
        }
        foreach ($config['pollers'] as $name => $consumer) {
            $this->loadPoller($name, $consumer, $container);
        }

        foreach ($config['producers'] as $name => $producer) {
            $this->loadProducer($name, $producer, $container);
        }
    }

    private function loadConnection(array $connection, ContainerBuilder $container)
    {
        $definition = new Definition($container->getParameter('radish.broker.connection.class'));
        $definition->setArguments([
            new Reference('radish.broker.amqp_factory'),
            $connection
        ]);

        $container->setDefinition('radish.broker.connection', $definition);
    }

    private function loadConsumer($name, array $consumer, ContainerBuilder $container)
    {
        $workers = [];
        foreach ($consumer['queues'] as $queueName => $queue) {
            $workers[$queueName] = new Reference($queue['worker']);
        }

        $definition = new DefinitionDecorator('radish.consumer');

        $args = [
            array_keys($consumer['queues']),
            $consumer['middleware'],
            $workers
        ];

        $definition->setArguments($args);

        $container->setDefinition(sprintf('radish.consumer.%s', $name), $definition);
    }

    public function loadPoller($name, array $poller, ContainerBuilder $container)
    {
        $workers = [];
        foreach ($poller['queues'] as $queueName => $queue) {
            $workers[$queueName] = new Reference($queue['worker']);
        }

        $definition = new DefinitionDecorator('radish.poller');

        $args = [
            array_keys($poller['queues']),
            $poller['middleware'],
            $workers,
            $poller['interval']
        ];

        $definition->setArguments($args);

        $container->setDefinition(sprintf('radish.poller.%s', $name), $definition);
    }

    private function loadProducer($name, array $producer, ContainerBuilder $container)
    {
        $definition = new DefinitionDecorator('radish.producer');
        $definition->setArguments([
            $producer['exchange']
        ]);

        $container->setDefinition(sprintf('radish.producer.%s', $name), $definition);
    }
}
