<?php

namespace Ludo\Server\Command;

use Ludo\Server\Server;
use Ludo\Support\Facades\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Swoole\Process;
use ReflectionClass;
use ReflectionException;
use phpDocumentor\Reflection\DocBlockFactory;


/**
 * Server Command
 *
 * @package Ludo\Server\Command
 */
class ServerCommand extends Command
{
    /**
     * @var string $pidFile pid filename
     */
    protected string $pidFile;

    /**
     * @var array $config server config
     */
    protected array $config;

    public function __construct()
    {
        parent::__construct('server');
    }

    /**
     * Configure the current command
     */
    protected function configure(): void
    {
        $this->setDescription('Start server.');
        $this->addArgument('name', InputOption::VALUE_REQUIRED, 'The name of server.');
        $this->addOption('command', 'c', InputOption::VALUE_REQUIRED, 'The command with server.', 'start');
        $this->addOption('list', 'l', InputOption::VALUE_NONE, 'List available server.');
    }

    /**
     * Execute the current command
     *
     * @param InputInterface $input input handle
     * @param OutputInterface $output output handle
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $config = Config::get('servers');

        if (!empty($input->getOption('list'))) {
            try {
                foreach ($config['servers'] as $name => $item) {
                    $message = sprintf('<fg=green>%s</>', $name);
                    if (!empty($item['class'])) {
                        $reflection = new ReflectionClass($item['class']);
                        $doc = DocBlockFactory::createInstance()->create($reflection->getDocComment());
                        $message .= sprintf(' <fg=default>%s</>', $doc->getDescription());
                    }
                    $output->writeln($message);
                }
            } catch (ReflectionException $e) {
                $output->writeln(sprintf('<fg=red>Server %s can not instantiate.</>', $input['class']));
            }

            return;
        }

        $serverName = $input->getArgument('name');

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
     * @param string $serverName server name
     * @param OutputInterface $output output handle
     * @return bool
     */
    protected function start(string $serverName, OutputInterface $output): bool
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
     * @param string $serverName server name
     * @param OutputInterface $output output handle
     * @return bool
     */
    protected function stop(string $serverName, OutputInterface $output): bool
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
     * @param string $serverName server name
     * @param OutputInterface $output output handle
     * @return bool
     */
    protected function reload(string $serverName, OutputInterface $output): bool
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