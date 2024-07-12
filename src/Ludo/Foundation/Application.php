<?php

namespace Ludo\Foundation;

use Ludo\Exception\ApplicationException;
use Ludo\Routing\Router;
use Ludo\Routing\Controller;
use ReflectionMethod;
use BadMethodCallException;
use Ludo\Support\Facades\Context;
use Ludo\Support\Facades\Config;
use Throwable;


/**
 * Class Application
 *
 * @package Ludo\Foundation
 */
class Application
{
    /**
     * auto route
     *
     * @param string $path access path
     * @return mixed
     * @throws ApplicationException
     */
    public function run(string $path = ''): mixed
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

            //if you have output, means this action is an ajax call.
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
            return;
        } catch (Throwable $e) {
            error_log($e);
            throw new ApplicationException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
