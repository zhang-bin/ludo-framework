<?php

namespace Ludo\Log;

use Exception;
use Ludo\Support\Facades\Config;
use Ludo\Support\Facades\Context;
use Monolog\Handler\NullHandler;
use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;


class Logger
{
    private $logger;

    /**
     * Logger constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->logger = new Monolog(Config::get('app.name'));
        $dir = LD_LOG_PATH . DIRECTORY_SEPARATOR . date(DATE_FORMAT) . DIRECTORY_SEPARATOR . date('G') . DIRECTORY_SEPARATOR;

        if (Config::get('app.debug')) {
            $this->logger->pushHandler(new StreamHandler($dir . 'debug.log', Monolog::DEBUG));
        } else {
            $this->logger->pushHandler(new NullHandler(Monolog::DEBUG));
        }
        $this->logger->pushHandler(new StreamHandler($dir . 'info.log', Monolog::INFO));
        $this->logger->pushHandler(new StreamHandler($dir . 'notice.log', Monolog::NOTICE));
        $this->logger->pushHandler(new StreamHandler($dir . 'warning.log', Monolog::WARNING));
        $this->logger->pushHandler(new StreamHandler($dir . 'error.log', Monolog::ERROR));
        $this->logger->pushHandler(new StreamHandler($dir . 'critical.log', Monolog::CRITICAL));
        $this->logger->pushHandler(new StreamHandler($dir . 'alert.log', Monolog::ALERT));
        $this->logger->pushHandler(new StreamHandler($dir . 'emergency.log', Monolog::EMERGENCY));
    }

    /**
     * Log a debug message to the logs
     *
     * @param string $message
     */
    public function debug(string $message): void
    {
        $this->write(__FUNCTION__, $message);
    }

    /**
     * Log an information message to the logs
     *
     * @param string $message
     */
    public function info(string $message): void
    {
        $this->write(__FUNCTION__, $message);
    }

    /**
     *  Log a notice message to the logs
     *
     * @param string $message
     */
    public function notice(string $message): void
    {
        $this->write(__FUNCTION__, $message);
    }

    /**
     * Log a warning message to the logs
     *
     * @param string $message
     */
    public function warning(string $message): void
    {
        $this->write(__FUNCTION__, $message);
    }

    /**
     *  Log an error message to the logs
     *
     * @param string $message
     */
    public function error(string $message): void
    {
        $this->write(__FUNCTION__, $message);
    }

    /**
     *  Log a critical message to the logs
     *
     * @param string $message
     */
    public function critical(string $message): void
    {
        $this->write(__FUNCTION__, $message);
    }

    /**
     * Log a alert message to the logs
     *
     * @param string $message
     */
    public function alert(string $message): void
    {
        $this->write(__FUNCTION__, $message);
    }

    /**
     * Log an emergency message to the logs
     *
     * @param string $message
     */
    public function emergency(string $message): void
    {
        $this->write(__FUNCTION__, $message);
    }

    /**
     * Log message to logs
     *
     * @param string $level
     * @param string $message
     */
    protected function write(string $level, string $message): void
    {
        $backtrace = debug_backtrace();
        $context = [
            'ctrl' => Context::get('current-controller'),
            'act' => Context::get('current-action'),
            'file' => $backtrace[2]['file'],
            'line' => $backtrace[2]['line'],
        ];

        $this->logger->{$level}($message, $context);
    }
}
