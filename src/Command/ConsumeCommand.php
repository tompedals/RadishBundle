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

class ConsumeCommand extends ContainerAwareCommand
{
    public function __construct()
    {
        parent::__construct('radish:consume');
    }

    public function configure()
    {
        $this->addArgument('consumer', InputArgument::REQUIRED, 'The name of the consumer to consume');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $consumerName = $input->getArgument('consumer');
        $this->getContainer()->get(sprintf('radish.consumer.%s', $consumerName))->consume();
    }
}
