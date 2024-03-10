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

namespace Laucov\WebFramework\Providers;

use Laucov\WebFramework\Config\Interfaces\ConfigInterface;
use Laucov\WebFramework\Services\DatabaseService;
use Laucov\WebFramework\Services\FileSessionService;
use Laucov\WebFramework\Services\Interfaces\ServiceInterface;
use Laucov\WebFramework\Services\Interfaces\SessionServiceInterface;
use Laucov\WebFramework\Services\LanguageService;
use Laucov\WebFramework\Services\ViewService;

/**
 * Caches and provides service object instances.
 */
class ServiceProvider
{
    /**
     * Cached instances.
     * 
     * @var array<string, ServiceInterface>
     */
    protected array $instances = [];

    /**
     * Create the provider instance.
     */
    public function __construct(
        /**
         * Configuration provider.
         */
        protected ConfigProvider $config,
    ) {
    }

    /**
     * Get the database service.
     */
    public function db(): DatabaseService
    {
        return $this->getService(DatabaseService::class);
    }

    /**
     * Get the language service.
     */
    public function lang(): LanguageService
    {
        return $this->getService(LanguageService::class);
    }

    /**
     * Get the session service.
     */
    public function session(): SessionServiceInterface
    {
        return $this->getService(FileSessionService::class);
    }

    /**
     * Get the view service.
     */
    public function view(): ViewService
    {
        return $this->getService(ViewService::class);
    }

    /**
     * Cache a new service instance.
     * 
     * @template T of ServiceInterface
     * @param class-string<T> $service
     * @return T
     */
    protected function getService(string $class_name): mixed
    {
        // Check for a cached instance.
        if (array_key_exists($class_name, $this->instances)) {
            return $this->instances[$class_name];
        }

        // Get constructor parameters.
        $reflection = new \ReflectionClass($class_name);
        $constructor = $reflection->getMethod('__construct');
        $parameters = $constructor->getParameters();

        // Create arguments.
        $arguments = [];
        foreach ($parameters as $parameter) {
            // Get and check type.
            $type = $parameter->getType();
            if ($type === null) {
                $msg = 'Untyped argument found in provided service ' .
                    "{$class_name} constructor.";
                throw new \RuntimeException($msg);
            }
            if (!($type instanceof \ReflectionNamedType)) {
                $msg = 'Intersection or union type argument found in '
                    . "provided service {$class_name} constructor.";
                throw new \RuntimeException($msg);
            }
            // Get name.
            $name = $type->getName();
            // Inject dependency.
            if (is_a($name, ServiceInterface::class, true)) {
                $arguments[] = $this->getService($name);
            } elseif (is_a($name, ConfigInterface::class, true)) {
                $arguments[] = $this->config->getConfig($name);
            } else {
                $msg = 'Invalid argument type "%s" found in provided ' .
                    "service {$class_name} constructor.";
                throw new \RuntimeException($msg);
            }
        }

        // Create and cache the instance.
        $service = new $class_name(...$arguments);
        $this->instances[$class_name] = $service;

        return $service;
    }
}
