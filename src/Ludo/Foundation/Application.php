<?php

namespace Ludo\Foundation;

use Ludo\Routing\Router;
use Ludo\Routing\Controller;
use Ludo\Support\ServiceProvider;
use ReflectionMethod;
use BadMethodCallException;
use Ludo\Support\Facades\Context;
use Ludo\Support\Facades\Config;


class Application
{
    /**
     * auto route
     *
     * @param string $path
     * @return string|void
     */
    public function run(string $path = '')
    {
        Context::set('begin-timestamp', microtime(true));

        try {
            if (PHP_SAPI != 'cli') {
                $pathInfo = str_replace('.html', '', $_SERVER['PATH_INFO']);
            } else {
                $pathInfo = $path;
            }

            [$ctrl, $act] = Router::parse($pathInfo);

            Context::set('current-controller', $ctrl);
            Context::set('current-action', $act);

            $ctrl = Config::get('app.controller') . $ctrl;
            /**
             * @var Controller $controller
             */
            $controller = new $ctrl();
            $action = $act;

            if (!method_exists($controller, $action)) {
                throw new BadMethodCallException(sprintf('Method [%s] Not Found', $ctrl->$action));
            }

            $method = new ReflectionMethod($ctrl, $action);
            if ($method->isStatic()) {
                throw new BadMethodCallException(sprintf('Method [%s] Can not Static', $ctrl->$action));
            }

            $output = $controller->beforeAction($action);
            if (empty($output)) {
                $output = $method->invoke($controller);
                $controller->afterAction($action, $output);
            }

            //if have output, means this action is an ajax call.
            if (!empty($output)) {
                if (!empty($controller->httpHeader)) {
                    if (!is_array($controller->httpHeader)) {
                        header($controller->httpHeader);
                    } else {
                        foreach ($controller->httpHeader as $header) {
                            header($header);
                        }
                    }
                }
                is_array($output) && $output = json_encode($output);
                return $output;
            }
            if (Config::get('app.debug')) {
                self::debug($output);
            }
        } catch (\Throwable $ex) {
            error_log($ex);
            if (Config::get('app.debug')) {
                $error = '<pre>' . $ex->getMessage() . "\n\n" . $ex->getTraceAsString() . '</pre>';
                echo $error;
                self::debug($error);
            }
        }
    }

    public static function debug($lastOutput = '')
    {
        $debugInfo = '<h2>Time:' . date('Y-m-d H:i:s') . ':' . currentUrl() . '</h2>';
        $debugInfo .= '@@@@error:<pre>' . var_export(error_get_last(), true) . '</pre>@@@@<br />';
        if (!empty($lastOutput)) {
            $debugInfo .= '@@@@output:<pre>' . htmlentities($lastOutput, ENT_QUOTES) . '</pre>@@@@';
        }

        $connections = ServiceProvider::getInstance()->getDBManagerHandler()->getConnections();
        if (!empty($connections)) {
            foreach ($connections as $connection) {
                /**
                 * @var \Ludo\Database\Connection $connection
                 */
                $debugInfo .= $connection->debug();
            }
        }

        $debugInfo .= '<h2>GET:</h2><pre>' . (!empty($_GET) ? var_export($_GET, true) : '') . '</pre>';
        $debugInfo .= '<h2>POST:</h2><pre>' . (!empty($_POST) ? var_export($_POST, true) : '') . '</pre>';
        $debugInfo .= '<h2>COOKIE:</h2><pre>' . (!empty($_COOKIE) ? var_export($_COOKIE, true) : '') . '</pre>';
        $debugInfo .= '<h2>SESSION:</h2><pre>' . (!empty($_SESSION) ? var_export($_SESSION, true) : '') . '</pre>';
        $debugInfo .= '<h2>FILES:</h2><pre>' . (!empty($_FILES) ? var_export($_FILES, true) : '') . '</pre>';
        $debugInfo .= '<h2>SERVER:</h2><pre>' . (!empty($_SERVER) ? var_export($_SERVER, true) : '') . '</pre>';
        $debugInfo .= '<h2>ENV:</h2><pre>' . (!empty($_ENV) ? var_export($_ENV, true) : '') . '</pre>';
        $debugInfo = str_replace('<?', '&lt;?', $debugInfo);
        $debugInfo = str_replace('?>', '&gt;?', $debugInfo);

        $debugFile = LD_UPLOAD_PATH . '/debug_console.php';
        $debugUrl = LD_UPLOAD_URL . '/debug_console.php';


        if (file_exists("config.php")) {
            $prefix = '<?php include_once("../config.php");';
        } else {
            $prefix = '<?php include_once("../config.inc.php");';
        }
        $prefix .= 'if (DEBUG) : ' .
            'if(@$_GET["clear"]) {' .
            'file_put_contents("' . $debugFile . '", ""); ' .
            'header("location:' . $debugUrl . '");' .
            '}	?>';
        $postfix = '<?php endif; ?>';

        $oldDebugInfo = file_get_contents($debugFile);

        $oldDebugInfo = str_replace($prefix, '', $oldDebugInfo);
        $oldDebugInfo = str_replace($postfix, '', $oldDebugInfo);

        $delimiter = '<br><br><br><br><br>=========================================================================================================================';
        $oldDebugInfo = $debugInfo . $delimiter . $oldDebugInfo;
        $arr = explode($delimiter, $oldDebugInfo);

        $cnt = count($arr);

        if ($cnt > 5) unset($arr[5]);

        $debugInfo = implode($delimiter, $arr);

        $debugOutput = $prefix . $debugInfo . $postfix;
        file_put_contents($debugFile, $debugOutput);
    }
}
