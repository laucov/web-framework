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

use Laucov\WebFramework\Data\ArrayBuilder;

/**
 * Stores routes and assign them to HTTP requests.
 */
class Router
{
    /**
     * Allowed return types for route closures.
     */
    public const CLOSURE_RETURN_TYPES = [
        'string',
        ResponseInterface::class,
        \Stringable::class,
    ];

    /**
     * Active patterns.
     * 
     * @var array<string, string>
     */
    protected array $patterns = [];

    /**
     * Active path prefixes.
     * 
     * @var array<string>
     */
    protected array $prefixes = [];

    /**
     * Registered routes.
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
     * Remove the last pushed path prefix.
     */
    public function popPrefix(): static
    {
        // Remove last prefix.
        array_pop($this->prefixes);
        return $this;
    }

    /**
     * Prefix the path for the next registered routes.
     * 
     * Active prefixes will not be replaced but incremented.
     */
    public function pushPrefix(string $path): static
    {
        // Add new prefix.
        $this->prefixes[] = trim($path, '/');
        return $this;
    }

    /**
     * Route a request.
     */
    public function route(RequestInterface $request): ResponseInterface
    {
        // Find the route.
        $path = $request->getUri()->path;
        $route = $this->findRoute($path);
        $closure = $route->closure;
        if ($closure === null) {
            throw new \RuntimeException('Route not found.');
        }

        // Get captured paramteres.
        $captured = $route->getParameters();
        $index = 0;

        // Fill arguments.
        $arguments = [];
        $reflection = new \ReflectionFunction($closure);
        $parameters = $reflection->getParameters();
        foreach ($parameters as $parameter) {
            /** @var \ReflectionNamedType */
            $type = $parameter->getType();
            $type_name = $type->getName();
            switch (true) {
                case is_a($type_name, RequestInterface::class, true):
                    $arguments[] = $request;
                    break;
                case $type_name === 'string':
                    $arguments[] = $captured[$index];
                    $index++;
                    break;
                default:
                    $message = 'Unsupported route argument of type %s.';
                    throw new \RuntimeException(sprintf($message, $type_name));
            }
        }

        // Run the function.
        $result = call_user_func_array($closure, $arguments);

        // Check if returned a string.
        if (is_string($result) || $result instanceof \Stringable) {
            $response = new OutgoingResponse();
            return $response->setBody("{$result}");
        }

        // Check if returned a response.
        if ($result instanceof ResponseInterface) {
            return $result;
        }

        // Unexpected value returned.
        // @codeCoverageIgnoreStart
        $message = 'Unsupported route return type %s.';
        throw new \RuntimeException(sprintf($message, gettype($result)));
        // @codeCoverageIgnoreEnd
    }

    /**
     * Add a path pattern.
     */
    public function setPattern(string $name, string $pattern): static
    {
        $this->patterns[$name] = $pattern;
        return $this;
    }

    /**
     * Add a route.
     */
    public function setRoute(string $path, \Closure $closure): static
    {
        // Check closure return type.
        foreach ($this->getClosureReturnTypes($closure) as $return_type) {
            if (!in_array($return_type, static::CLOSURE_RETURN_TYPES)) {
                $message = sprintf(
                    'Invalid route closure return type. Allowed types are: %s.',
                    implode(', ', static::CLOSURE_RETURN_TYPES),
                );
                throw new \InvalidArgumentException($message);
            }
        }

        // Trim path.
        $path = trim($path, '/');
        if (count($this->prefixes) > 0) {
            $path = implode('/', $this->prefixes) . '/' . $path;
        }

        // Add route.
        $segments = explode('/', $path);
        $segments[] = '/';
        $this->routes->setValue($segments, $closure);

        return $this;
    }

    /**
     * Find a compatible route for a given path.
     */
    protected function findRoute(string $path): Route
    {
        // Split the path.
        $segments = explode('/', $path);

        // Dive the routes array.
        $routes = $this->routes->getArray();
        $route = new Route();
        foreach ($segments as $segment) {
            // Check for direct match.
            if (array_key_exists($segment, $routes)) {
                $routes = $routes[$segment];
                continue;
            }
            // Match patterns.
            foreach ($this->patterns as $name => $patt) {
                if (!array_key_exists(':' . $name, $routes)) {
                    continue;
                }
                if (preg_match($patt, $segment) === 1) {
                    $routes = $routes[':' . $name];
                    $route->addParameter($segment);
                    continue 2;
                }
            }
            // Could not find a match for this segment.
            return $route;
        }

        // Check if a closure exists.
        if (!array_key_exists('/', $routes)) {
            return $route;
        }
        $closure = $routes['/'];

        // Check if is a closure.
        if (!($closure instanceof \Closure)) {
            // @codeCoverageIgnoreStart
            $message = 'Found a value of type %s stored as a route closure.';
            throw new \RuntimeException($message);
            // @codeCoverageIgnoreEnd
        }

        $route->closure = $closure;
        return $route;
    }

    /**
     * Get the return types names from a given closure.
     * 
     * @return array<string>
     */
    protected function getClosureReturnTypes(\Closure $closure): array
    {
        // Get the reflection object.
        $reflection = new \ReflectionFunction($closure);
        $return_type = $reflection->getReturnType();

        // Return type names.
        return $return_type !== null
            ? $this->getReflectionTypeNames($return_type)
            : [];
    }

    /**
     * Get the names of types represented by a `ReflectionType` object.
     * 
     * @return array<string>
     */
    protected function getReflectionTypeNames(\ReflectionType $type): array
    {
        // Get single return type name.
        if ($type instanceof \ReflectionNamedType) {
            return [$type->getName()];
        }

        // Get multiple return type names.
        if (
            $type instanceof \ReflectionUnionType
            || $type instanceof \ReflectionIntersectionType
        ) {
            return array_merge(...array_map(
                [$this, 'getReflectionTypeNames'],
                $type->getTypes(),
            ));
        }

        // Unexpected ReflectionType found.
        // @codeCoverageIgnoreStart
        $message = 'Unsupported ReflectionType class %s.';
        throw new \RuntimeException(sprintf($message, get_class($type)));
        // @codeCoverageIgnoreEnd
    }
}
