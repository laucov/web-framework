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
class RouteClosure
{
    /**
     * Allowed closure parameter types.
     */
    const ALLOWED_PARAMETER_TYPES = [
        'string',
        RequestInterface::class,
    ];

    /**
     * Allowed closure return types.
     */
    const ALLOWED_RETURN_TYPES = [
        'string',
        \Stringable::class,
        ResponseInterface::class,
    ];

    /**
     * Registered closure.
     */
    public readonly \Closure $closure;

    /**
     * Closure parameter types.
     * 
     * @var array<string>
     */
    public readonly array $parameterTypes;

    /**
     * Closure return type.
     */
    public readonly string $returnType;

    /**
     * Closure reflection.
     */
    protected \ReflectionFunction $reflection;

    /**
     * Create the route closure instance.
     */
    public function __construct(\Closure $closure)
    {
        // Save the closure's reflection object.
        $this->reflection = new \ReflectionFunction($closure);

        // Get and validate the parameter types.
        $parameter_types = $this->findClosureParameterTypes();
        if ($parameter_types === false) {
            $message = 'Closure has one or more invalid parameter types.';
            throw new \InvalidArgumentException($message);
        }

        // Get and validate the return type.
        $return_type = $this->findClosureReturnType();
        if ($return_type === false) {
            $message = 'Closure has an invalid return type.';
            throw new \InvalidArgumentException($message);
        }

        // Assign properties.
        $this->parameterTypes = $parameter_types;
        $this->returnType = $return_type;
        $this->closure = $closure;
    }

    /**
     * Find the closure's return type name.
     * 
     * Will return `false` if the closure has an invalid return type.
     */
    protected function findClosureReturnType(): false|string
    {
        // Get the ReflectionType object and check if is a named type.
        $return_type = $this->reflection->getReturnType();
        if (!($return_type instanceof \ReflectionNamedType)) {
            return false;
        }

        // Get the return type name.
        $name = $return_type->getName();
        if (!in_array($name, static::ALLOWED_RETURN_TYPES)) {
            return false;
        }

        return $name;
    }

    /**
     * Find the closure's parameter type names.
     * 
     * Will return `false` if the closure has invalid parameter types.
     * 
     * @return false|array<string>
     */
    protected function findClosureParameterTypes(): false|array
    {
        // Initialize type array.
        $names = [];
        
        // Get and validate parameters.
        $parameters = $this->reflection->getParameters();
        foreach ($parameters as $parameter) {
            // Check if is not union or intersection type.
            $type = $parameter->getType();
            if (!($type instanceof \ReflectionNamedType)) {
                return false;
            }
            // Get and check type name.
            $name = $type->getName();
            if (!in_array($name, static::ALLOWED_PARAMETER_TYPES)) {
                return false;
            }
            $names[] = $name;
        }

        return $names;
    }
}
