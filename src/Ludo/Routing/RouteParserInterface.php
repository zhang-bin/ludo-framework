<?php

namespace Ludo\Routing;


/**
 * Interface RouteParserInterface
 *
 * Contract for custom route parsers. Implement this interface in your own
 * project and register it via the `app.route_parser` config item (or
 * Application::setRouteParser()) to override the default path parsing.
 *
 * @package Ludo\Routing
 */
interface RouteParserInterface
{
    /**
     * Parse the access path into a [controller, action] pair.
     *
     * @param string $pathInfo access path
     * @return array a tuple of [string $ctrl, string $act]
     */
    public function parse(string $pathInfo): array;
}
