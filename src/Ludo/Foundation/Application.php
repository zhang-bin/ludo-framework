<?php

namespace Ludo\Foundation;

use Ludo\Exception\ApplicationException;
use Ludo\Routing\Router;
use Ludo\Routing\Controller;
use Ludo\Routing\RouteParserInterface;
use ReflectionMethod;
use BadMethodCallException;
use InvalidArgumentException;
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
     * Resolve the route parser to use.
     *
     * Reads the `app.route_parser` config item (a {@see RouteParserInterface}
     * class name or instance). Falls back to the built-in {@see Router::parse()}
     * when it is not configured.
     *
     * @return callable
     * @throws InvalidArgumentException when the configured parser is invalid
     */
    protected function resolveRouteParser(): callable
    {
        $parser = Config::get('app.route_parser');
        if (empty($parser)) {
            return [Router::class, 'parse'];
        }

        is_string($parser) && $parser = new $parser();
        if (!$parser instanceof RouteParserInterface) {
            throw new InvalidArgumentException('app.route_parser must implement ' . RouteParserInterface::class);
        }

        return [$parser, 'parse'];
    }

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

            $parser = $this->resolveRouteParser();
            [$ctrl, $act] = $parser($pathInfo);

            Context::set('current-controller', $ctrl);
            Context::set('current-action', $act);

            $ctrl = Config::get('app.controller') . $ctrl;
            /**
             * @var Controller $controller
             */
            $controller = new $ctrl();
            $action = $act;

            if (!method_exists($controller, $action)) {
                throw new BadMethodCallException(sprintf('Method [%s] Not Found', $ctrl . '::' . $action));
            }

            $method = new ReflectionMethod($ctrl, $action);
            if ($method->isStatic()) {
                throw new BadMethodCallException(sprintf('Method [%s] Can not Static', $ctrl . '::' . $action));
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
            return null;
        } catch (Throwable $e) {
            error_log((string)$e);
            throw new ApplicationException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
