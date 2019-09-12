<?php

namespace Ludo\AsyncTask\Command;

use Ludo\AsyncTask\MessageQueue\MessageQueueFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FlushMessageCommand extends Command
{
    public function __construct()
    {
        parent::__construct('channel:flush');
    }

    protected function configure()
    {
        $this->setDescription('Flush all message.');
        $this->addOption('channel', 'C', InputOption::VALUE_OPTIONAL, 'The channel name of queue.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $channel = $input->getOption('channel');

        $factory = new MessageQueueFactory();
        $messageQueue = $factory->get();

        $messageQueue->flush($channel);

        $output->writeln('<fg=red>Flush all message.</>');
    }
}