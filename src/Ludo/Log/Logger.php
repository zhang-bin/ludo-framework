<?php
namespace Ludo\Log;

/* Finally, A light, permissions-checking logging class.
 *
 * Author	: Kenneth Katzgrau < katzgrau@gmail.com >
 * Date	: July 26, 2008
 * Comments	: Originally written for use with wpSearch
 * Website	: http://codefury.net
 * Version	: 1.0
 *
 * Usage:
 *		$log = new LdLogger(LdLogger::INFO);
 *		$log->info("Returned a million search results");	//Prints to the log file
 *		$log->fatal("Oh dear.");				//Prints to the log file
 *		$log->debug("x = 5");					//Prints nothing due to priority setting
*/

class Logger
{
    private static $instance;

    private static $_logFilename = [
        LOGGER_LEVEL_INFO => 'info',
        LOGGER_LEVEL_DEBUG => 'debug',
        LOGGER_LEVEL_WARN => 'warning',
        LOGGER_LEVEL_ERROR => 'error',
        LOGGER_LEVEL_FATAL => 'fatal'
    ];

    public function __construct()
    {
        $dir = LD_LOG_PATH.DIRECTORY_SEPARATOR.date(DATE_FORMAT).DIRECTORY_SEPARATOR.date('G').DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            $oldMask = umask(0);
            mkdir($dir, 0777, true);
            umask($oldMask);
        }
        self::$_logFilename = array(
            LOGGER_LEVEL_INFO => $dir.'info.log',
            LOGGER_LEVEL_DEBUG => $dir.'debug.log',
            LOGGER_LEVEL_WARN => $dir.'warning.log',
            LOGGER_LEVEL_ERROR => $dir.'error.log',
            LOGGER_LEVEL_FATAL => $dir.'fatal.log'
        );
    }

    /**
     * @return Logger
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 记录LOGGER_LEVEL_INFO级别的日志
     *
     * @param string $info 日志内容
     * @return Logger
     */
    public function info($info)
    {
        $this->log($info, LOGGER_LEVEL_INFO);
        return self::$instance;
    }

    /**
     * 记录LOGGER_LEVEL_DEBUG级别的日志
     *
     * @param string $info 日志内容
     * @return Logger
     */
    public function debug($info)
    {
        $this->log($info, LOGGER_LEVEL_DEBUG);
        return self::$instance;
    }

    /**
     * 记录LOGGER_LEVEL_WARN级别的日志
     *
     * @param string $info 日志内容
     * @return Logger
     */
    public function warn($info)
    {
        $this->log($info, LOGGER_LEVEL_WARN);
        return self::$instance;
    }

    /**
     * 记录LOGGER_LEVEL_ERROR级别的日志
     *
     * @param string $info 日志内容
     * @return Logger
     */
    public function error($info)
    {
        $this->log($info, LOGGER_LEVEL_ERROR);
        return self::$instance;
    }

    /**
     * 记录LOGGER_LEVEL_FATAL级别的日志
     *
     * @param string $info 日志内容
     * @return Logger
     */
    public function fatal($info)
    {
        $this->log($info, LOGGER_LEVEL_FATAL);
        return self::$instance;
    }

    /**
     * 记录日志, 如果日志级别小于default,那么不记录日志
     *
     * @param string $info 日志内容
     * @param int $priority 当前日志级别,例如LOGGER_LEVEL_FATAL,LOGGER_LEVEL_INFO
     */
    public function log($info, $priority)
    {
        //== get file and line from debug_backtrace.
        $backtrace = debug_backtrace();
        $file = $backtrace[2]['file'];
        $line = $backtrace[2]['line'];
        $class = CURRENT_CONTROLLER.'::';
        $function = CURRENT_ACTION.'-->';

        $status = $this->getTimeLine($priority);
        $log = $status.$class.$function.$info;
        if (!empty($file)) $log .= " in $file on line $line";
        $log .= "\n";
        $this->save($priority, $log);
    }

    /**
     * 记录日志
     *
     * @param int $priority 日志级别
     * @param string $log 内容
     * @return mixed
     */
    public function save($priority, $log)
    {
        if (!empty($log)) {
            $filename = self::$_logFilename[$priority];
            $fp = fopen($filename, 'a');
            fwrite($fp, $log);
            fclose($fp);
        }
        return self::$instance;
    }

    private function getTimeLine($level)
    {
        $time = date(TIME_FORMAT);

        switch($level) {
            case LOGGER_LEVEL_INFO:
                $line = "$time - INFO  --> ";
                break;
            case LOGGER_LEVEL_WARN:
                $line = "$time - WARN  --> ";
                break;
            case LOGGER_LEVEL_DEBUG:
                $line = "$time - DEBUG --> ";
                break;
            case LOGGER_LEVEL_ERROR:
                $line = "$time - ERROR --> ";
                break;
            case LOGGER_LEVEL_FATAL:
                $line = "$time - FATAL --> ";
                break;
            default:
                $line = "$time - LOG   --> ";
                break;
        }
        return $line;
    }
}
