<?php

namespace Radish\RadishBundle;

use Radish\RadishBundle\DependencyInjection\Compiler\RegisterMiddlewarePass;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RadishBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterMiddlewarePass());
    }

    public function registerCommands(Application $application)
    {
        $application->add($this->container->get('radish.command.consume'));
        $application->add($this->container->get('radish.command.poll'));
        $application->add($this->container->get('radish.command.setup'));
    }
}
