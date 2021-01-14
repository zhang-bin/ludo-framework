<?php

namespace Ludo\AsyncTask\Command;

use Ludo\AsyncTask\MessageQueue\MessageQueueFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Reload Message Command
 *
 * @package Ludo\AsyncTask\Command
 */
class ReloadMessageCommand extends Command
{
    public function __construct()
    {
        parent::__construct('channel:reload');
    }

    /**
     * Configure the current command
     */
    protected function configure(): void
    {
        $this->setDescription('Reload all failed message into waiting queue.');
        $this->addOption('channel', 'C', InputOption::VALUE_OPTIONAL, 'The channel name of queue.');
    }

    /**
     * Execute the current command
     *
     * @param InputInterface $input input handle
     * @param OutputInterface $output output handle
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $channel = $input->getOption('channel');

        $factory = new MessageQueueFactory();
        $messageQueue = $factory->get();

        $num = $messageQueue->reload($channel);

        $output->writeln(sprintf('<fg=green>Reload %d failed message into waiting queue.</>', $num));
    }
}