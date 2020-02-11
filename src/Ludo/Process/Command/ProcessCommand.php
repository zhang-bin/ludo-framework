<?php

namespace Ludo\Process\Command;

use Ludo\Support\Facades\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Swoole\Process as SwooleProcess;
use Ludo\Process\Process;
use ReflectionClass;
use ReflectionException;
use phpDocumentor\Reflection\DocBlockFactory;

class ProcessCommand extends Command
{
    protected $pidFile;

    protected $config;

    public function __construct()
    {
        parent::__construct('process');
    }

    protected function configure()
    {
        $this->setDescription('Start process.');
        $this->addArgument('name', InputOption::VALUE_REQUIRED, 'The name of process.');
        $this->addOption('command', 'c', InputOption::VALUE_REQUIRED, 'The command with process.', 'start');
        $this->addOption('list', 'l', InputOption::VALUE_NONE, 'List available process.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $config = Config::get('processes');

        if (!empty($input->getOption('list'))) {
            try {
                foreach ($config['processes'] as $name => $item) {
                    $reflection = new ReflectionClass($item['class']);
                    $doc = DocBlockFactory::createInstance()->create($reflection->getDocComment());
                    $output->writeln(sprintf('<fg=green>%s</> <fg=default>%s</>', $name, $doc->getDescription()));
                }
            } catch (ReflectionException $e) {
                $output->writeln(sprintf('<fg=red>Process %s can not instantiate.</>', $input['class']));
            }

            return;
        }

        $processName = $input->getArgument('name');
        $this->config = $config['processes'][$processName];
        $this->pidFile = $this->config['pid_file'];

        $command = $input->getOption('command');
        switch ($command) {
            case 'start':
                $this->start($processName, $output);
                break;
            case 'stop':
                $this->stop($processName, $output);
                break;
            case 'restart':
                $this->stop($processName, $output);
                sleep(1);
                $this->start($processName, $output);
                break;
            default:

                $output->writeln('<fg=red>Command not found.</>');
                break;
        }
    }

    /**
     * Start server
     *
     * @param string $processName
     * @param OutputInterface $output
     * @return bool
     */
    protected function start(string $processName, OutputInterface $output)
    {
        $pid = $this->getPid();
        if ($pid && SwooleProcess::kill($pid, 0)) {
            $output->writeln(sprintf('<fg=red>%s already exist.</>', $processName));
            return false;
        }

        $processClass = $this->config['class'];

        /**
         * @var Process $process
         */
        $process = new $processClass();
        $pid = $process->run();
        file_put_contents($this->pidFile, $pid);

        $output->writeln(sprintf('<fg=green>Start process %s successful.</>', $processName));
        return true;
    }

    /**
     * Stop server
     *
     * @param string $processName
     * @param OutputInterface $output
     * @return bool
     */
    protected function stop(string $processName, OutputInterface $output)
    {
        if (!$pid = $this->getPid()) {
            $output->writeln(sprintf('<fg=red>%s not start.</>', $processName));
            return false;
        }

        if (SwooleProcess::kill($pid, SIGTERM)) {
            $output->writeln(sprintf('<fg=green>Stop process %s successful.</>', $processName));
        } else {
            $output->writeln(sprintf('<fg=red>Stop process %s failed.</>', $processName));
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