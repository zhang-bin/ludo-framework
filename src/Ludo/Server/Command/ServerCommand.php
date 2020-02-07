<?php

namespace Ludo\Server\Command;

use Ludo\Server\Server;
use Ludo\Support\Facades\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Swoole\Process;

class ServerCommand extends Command
{
    protected $pidFile;

    protected $config;

    public function __construct()
    {
        parent::__construct('server');
    }

    protected function configure()
    {
        $this->setDescription('Start server.');
        $this->addArgument('name', InputOption::VALUE_REQUIRED, 'The name of server.');
        $this->addOption('command', 'C', InputOption::VALUE_REQUIRED, 'The command with server.', 'start');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $serverName = $input->getArgument('name');

        $config = Config::get('servers');
        $serverConfig = $config['servers'][$serverName];
        $this->config = $config['servers'][$serverName];

        $this->config['settings'] = array_replace($config['settings'], empty($serverConfig['settings']) ? [] : $serverConfig['settings']);
        $this->config['processes'] = array_replace($config['processes'], empty($serverConfig['processes']) ? [] : $serverConfig['processes']);
        $this->config['callbacks'] = array_replace($config['callbacks'], empty($serverConfig['callbacks']) ? [] : $serverConfig['callbacks']);

        $this->pidFile = $this->config['settings']['pid_file'];

        $command = $input->getOption('command');
        switch ($command) {
            case 'start':
                $this->start($serverName, $output);
                break;
            case 'stop':
                $this->stop($serverName, $output);
                break;
            case 'restart':
                $this->stop($serverName, $output);
                sleep(1);
                $this->start($serverName, $output);
                break;
            case 'reload':
                $this->reload($serverName, $output);
                break;
            default:
                $output->writeln('<fg=red>Command not found.</>');
                break;
        }
    }

    /**
     * Start server
     *
     * @param string $serverName
     * @param OutputInterface $output
     * @return bool
     */
    protected function start(string $serverName, OutputInterface $output)
    {
        $pid = $this->getPid();
        if ($pid && Process::kill($pid, 0)) {
            $output->writeln(sprintf('<fg=red>%s already exist.</>', $serverName));
            return false;
        }

        $server = new Server($serverName);
        $server->init($this->config);
        $server->start();

        $output->writeln(sprintf('<fg=green>Start server %s successful.</>', $serverName));
        return true;
    }

    /**
     * Stop server
     *
     * @param string $serverName
     * @param OutputInterface $output
     * @return bool
     */
    protected function stop(string $serverName, OutputInterface $output)
    {
        if (!$pid = $this->getPid()) {
            $output->writeln(sprintf('<fg=red>%s not start.</>', $serverName));
            return false;
        }

        if (Process::kill($pid)) {
            $output->writeln(sprintf('<fg=green>Stop server %s successful.</>', $serverName));
        } else {
            $output->writeln(sprintf('<fg=red>Stop server %s failed.</>', $serverName));
        }
        return true;
    }

    /**
     * Reload server
     *
     * @param string $serverName
     * @param OutputInterface $output
     * @return bool
     */
    protected function reload(string $serverName, OutputInterface $output)
    {
        if (!$pid = $this->getPid()) {
            $output->writeln(sprintf('<fg=red>%s not start.</>', $serverName));
            return false;
        }

        if (Process::kill($pid, SIGUSR1)) {
            $output->writeln(sprintf('<fg=green>Reload server %s successful.</>', $serverName));
        } else {
            $output->writeln(sprintf('<fg=red>Reload server %s failed.</>', $serverName));
        }
        return true;
    }

    /**
     * Get master pid
     *
     * @return bool|false|string
     */
    protected function getPid()
    {
        return file_exists($this->pidFile) ? file_get_contents($this->pidFile) : false;
    }
}