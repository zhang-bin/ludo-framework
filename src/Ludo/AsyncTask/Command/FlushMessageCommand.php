<?php

namespace Ludo\AsyncTask\Command;

use Ludo\AsyncTask\MessageQueue\MessageQueueFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Flush Message Command
 *
 * @package Ludo\AsyncTask\Command
 */
class FlushMessageCommand extends Command
{
    public function __construct()
    {
        parent::__construct('channel:flush');
    }

    /**
     * Configure the current command
     */
    protected function configure(): void
    {
        $this->setDescription('Flush all message.');
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

        $messageQueue->flush($channel);

        $output->writeln('<fg=red>Flush all message.</>');
    }
}