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
     * Custom route parser. A callable of signature `fn(string $pathInfo): array`,
     * or null to use the default resolution (config / Router::parse).
     *
     * @var callable|null
     */
    protected $routeParser = null;

    /**
     * Set a custom route parser.
     *
     * Accepts a {@see RouteParserInterface} instance or any callable with the
     * signature `fn(string $pathInfo): array` returning [string $ctrl, string $act].
     *
     * @param RouteParserInterface|callable $parser
     * @return $this
     */
    public function setRouteParser(RouteParserInterface|callable $parser): static
    {
        $this->routeParser = $parser instanceof RouteParserInterface ? [$parser, 'parse'] : $parser;
        return $this;
    }

    /**
     * Resolve the route parser to use.
     *
     * Resolution order:
     *   1. Parser set via {@see setRouteParser()}.
     *   2. The `app.route_parser` config item (a class name, a
     *      {@see RouteParserInterface} instance, or a callable).
     *   3. The built-in {@see Router::parse()}.
     *
     * @return callable
     * @throws InvalidArgumentException when the configured parser is invalid
     */
    protected function resolveRouteParser(): callable
    {
        if ($this->routeParser !== null) {
            return $this->routeParser;
        }

        $custom = Config::get('app.route_parser');
        if (!empty($custom)) {
            is_string($custom) && $custom = new $custom();

            if ($custom instanceof RouteParserInterface) {
                return [$custom, 'parse'];
            }

            if (is_callable($custom)) {
                return $custom;
            }

            throw new InvalidArgumentException('app.route_parser must be a RouteParserInterface instance or a callable');
        }

        return [Router::class, 'parse'];
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
