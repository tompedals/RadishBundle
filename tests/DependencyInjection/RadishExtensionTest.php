<?php

namespace Radish\RadishBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RadishExtensionTest extends TestCase
{
    public function testDefaultConnectionConfiguration()
    {
        $container = $this->createContainer();
        $this->loadConfig($container, []);

        $this->assertHasService($container, 'radish.broker.connection');
    }

    public function testConnectionConfiguration()
    {
        $container = $this->createContainer();
        $this->loadConfig($container, [
            'connection' => [
                'host' => '1.2.3.4',
                'port' => 1337,
                'login' => 'admin',
                'password' => 'password',
                'vhost' => '/',
            ]
        ]);

        $this->assertHasService($container, 'radish.broker.connection');
    }

    private function assertHasService(ContainerBuilder $container, $id)
    {
        $this->assertTrue($container->hasDefinition($id) || $container->hasAlias($id), sprintf('The service %s should be defined.', $id));
    }

    private function assertNotHasService(ContainerBuilder $container, $id)
    {
        $this->assertFalse($container->hasDefinition($id) || $container->hasAlias($id), sprintf('The service %s should not be defined.', $id));
    }

    private function loadConfig(ContainerBuilder $container, array $config = [])
    {
        $extension = new RadishExtension();

        $extension->load([$config], $container);
    }

    private function createContainer($debug = true)
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', $debug);

        return $container;
    }
}
