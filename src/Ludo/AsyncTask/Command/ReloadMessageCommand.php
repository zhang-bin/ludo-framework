<?php
namespace Ludo\AsyncTask\Command;

use Ludo\AsyncTask\MessageQueue\MessageQueueFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class ReloadMessageCommand extends Command
{
    public function __construct()
    {
        parent::__construct('channel:reload');
    }

    protected function configure()
    {
        $this->setDescription('Reload all failed message into waiting queue.');
        $this->addOption('channel', 'C', InputOption::VALUE_OPTIONAL, 'The channel name of queue.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $channel = $input->getOption('channel');

        $factory = new MessageQueueFactory();
        $messageQueue = $factory->get();

        $num = $messageQueue->reload($channel);

        $output->writeln('<fg=green>Reload %d failed message into waiting queue.</>', $num);
    }
}