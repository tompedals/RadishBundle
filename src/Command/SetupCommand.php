<?php

namespace Radish\RadishBundle\Command;

use Radish\Broker\Connection;
use Radish\Broker\ExchangeRegistry;
use Radish\Broker\QueueRegistry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends ContainerAwareCommand
{
    protected $exchangeRegistry;
    protected $queueRegistry;

    public function __construct(ExchangeRegistry $exchangeRegistry, QueueRegistry $queueRegistry)
    {
        $this->exchangeRegistry = $exchangeRegistry;
        $this->queueRegistry = $queueRegistry;

        parent::__construct('queue:setup');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->exchangeRegistry->setUp();
        $this->queueRegistry->setUp();
    }
}