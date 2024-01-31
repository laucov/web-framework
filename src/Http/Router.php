<?php

/**
 * This file is part of Laucov's Web Framework project.
 * 
 * Copyright 2024 Laucov Serviços de Tecnologia da Informação Ltda.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @package web-framework
 * 
 * @author Rafael Covaleski Pereira <rafael.covaleski@laucov.com>
 * 
 * @license <http://www.apache.org/licenses/LICENSE-2.0> Apache License 2.0
 * 
 * @copyright © 2024 Laucov Serviços de Tecnologia da Informação Ltda.
 */

namespace Laucov\WebFramework\Http;

use Laucov\Arrays\ArrayBuilder;

/**
 * Stores routes and assign them to HTTP requests.
 */
class Router
{
    /**
     * Stored patterns.
     * 
     * @var array<string, string>
     */
    protected array $patterns = [];

    /**
     * Active prefixes.
     * 
     * @var array<string>
     */
    protected array $prefixes = [];

    /**
     * Stored routes.
     */
    protected ArrayBuilder $routes;

    /**
     * Create the router instance.
     */
    public function __construct()
    {
        $this->routes = new ArrayBuilder([]);
    }

    /**
     * Find a route for the given request object.
     */
    public function findRoute(RequestInterface $request): null|Route
    {
        // Get method and routes.
        $method = $request->getMethod();
        $routes = (array) $this->routes->getValue($method, []);

        // Get path segments.
        $path = $request->getUri()->path;
        $segments = $path ? explode('/', $path) : [];
        $segments[] = '/';

        // Try to find a route.
        $result = $routes;
        $captured_segments = [];
        foreach ($segments as $segment) {
            // Check for direct match.
            if (array_key_exists($segment, $result)) {
                $result = $result[$segment];
                continue;
            }
            // Check for pattern match.
            foreach ($this->patterns as $name => $pattern) {
                // Ignore unused pattern.
                $key = ':' . $name;
                if (!array_key_exists($key, $result)) {
                    continue;
                }
                // Test the pattern and capture segment.
                if (preg_match($pattern, $segment) === 1) {
                    $result = $result[$key];
                    $captured_segments[] = $segment;
                    continue 2;
                }
            }
            // Route does not exist.
            return null;
        }

        // Check if is a route closure.
        if (!($result instanceof RouteClosure)) {
            // @codeCoverageIgnoreStart
            $message = 'Found an unexpected [%s] stored as a route closure.';
            throw new \RuntimeException(sprintf($message, gettype($result)));
            // @codeCoverageIgnoreEnd
        }

        // Fill function parameters.
        $parameters = [];
        $capture_index = 0;
        foreach ($result->parameterTypes as $type) {
            if ($type->name === 'string') {
                if ($type->isVariadic) {
                    // Add variadic string argument.
                    $slice = array_slice($captured_segments, $capture_index);
                    array_push($parameters, ...$slice);
                    $capture_index = count($captured_segments) - 1;
                } else {
                    // Add single string argument.
                    $parameters[] = $captured_segments[$capture_index];
                    $capture_index++;
                }
            } elseif (is_a($type->name, RequestInterface::class, true)) {
                // Add request dependency.
                $parameters[] = $request;
            } else {
                // @codeCoverageIgnoreStart
                $message = 'Unexpected route closure parameter of type [%s].';
                throw new \RuntimeException(sprintf($message, $type));
                // @codeCoverageIgnoreEnd
            }
        }

        return new Route($result, $parameters);
    }

    /**
     * Remove the last pushed prefix.
     */
    public function popPrefix(): static
    {
        array_pop($this->prefixes);
        return $this;
    }

    /**
     * Prefix the next routes with the given path.
     * 
     * Currently active prefixes will be added before the new prefix.
     */
    public function pushPrefix(string $path): static
    {
        $this->prefixes[] = trim($path, '/');
        return $this;
    }

    /**
     * Set a new pattern for route searches.
     */
    public function setPattern(string $name, string $regex): static
    {
        $this->patterns[$name] = $regex;
        return $this;
    }

    /**
     * Store a route for the given closure.
     */
    public function setRoute(
        string $method,
        string $path,
        \Closure $callback,
    ): static {
        // Get prefix segments.
        $prefix = implode('/', $this->prefixes);
        $prefix_segments = strlen($prefix) > 0 ? explode('/', $prefix) : [];

        // Get path segments.
        $path = trim($path, '/');
        $segments = $path ? explode('/', $path) : [];
        $segments[] = '/';

        // Store a new route closure.
        $keys = [$method, ...$prefix_segments, ...$segments];
        $route_closure = new RouteClosure($callback);
        $this->routes->setValue($keys, $route_closure);
        
        return $this;
    }
}
