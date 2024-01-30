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

/**
 * Stores information about an HTTP route.
 */
class Route
{
    /**
     * Execution parameters.
     */
    protected array $parameters;

    /**
     * Route closure.
     */
    protected RouteClosure $routeClosure;

    /**
     * Create the route instance.
     */
    public function __construct(RouteClosure $route_closure, array $parameters)
    {
        $this->routeClosure = $route_closure;
        $this->parameters = $parameters;
    }

    /**
     * Run the route's closure with the given arguments.
     */
    public function run(): ResponseInterface
    {
        // Get closure results.
        $result = call_user_func_array(
            $this->routeClosure->closure,
            $this->parameters,
        );

        // Return result as a response.
        if ($result instanceof ResponseInterface) {
            return $result;
        } elseif (is_string($result) || $result instanceof \Stringable) {
            $response = new OutgoingResponse();
            return $response->setBody((string) $result);
        } else {
            // @codeCoverageIgnoreStart
            $message = 'Received an unexpected result from a route closure.';
            throw new \RuntimeException($message);
            // @codeCoverageIgnoreEnd
        }
    }
}
