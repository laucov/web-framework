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

namespace Laucov\WebFwk\Providers;

use Laucov\Injection\Resolver;
use Laucov\WebFwk\Services\DatabaseService;
use Laucov\WebFwk\Services\FileSessionService;
use Laucov\WebFwk\Services\Interfaces\ServiceInterface;
use Laucov\WebFwk\Services\Interfaces\SessionServiceInterface;
use Laucov\WebFwk\Services\Interfaces\SmtpServiceInterface;
use Laucov\WebFwk\Services\LanguageService;
use Laucov\WebFwk\Services\PhpMailerSmtpService;
use Laucov\WebFwk\Services\ViewService;

/**
 * Caches and provides services (`ServiceInterface` objects).
 * 
 * Also provides cached configuration objects to those services.
 */
class ServiceProvider
{
    /**
     * Service dependencies.
     */
    protected ServiceDependencyRepository $dependencies;

    /**
     * Cached instances.
     * 
     * @var array<string, ServiceInterface>
     */
    protected array $instances = [];

    /**
     * Dependency resolver.
     */
    protected Resolver $resolver;

    /**
     * Registered services.
     * 
     * These services can be used to resolve function dependencies.
     * 
     * @var array<class-string<ServiceInterface>, string>
     */
    protected array $services = [];

    /**
     * Create the provider instance.
     */
    public function __construct(
        /**
         * Configuration provider.
         */
        protected ConfigProvider $config,
    ) {
        // Set dependencies.
        $this->dependencies = new ServiceDependencyRepository();
        $this->dependencies->setConfigProvider($this->config);
        $this->resolver = new Resolver($this->dependencies);

        // Get public methods.
        $reflection = new \ReflectionClass(static::class);
        /** @var array<\ReflectionMethod> */
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        // Register each service factory method as a dependency.
        foreach ($methods as $method) {
            // Get method return type.
            $type = $method->getReturnType();
            if ($type instanceof \ReflectionNamedType) {
                // Add factory dependency if returns a ServiceInterface object.
                $type_name = $type->getName();
                if (is_a($type_name, ServiceInterface::class, true)) {
                    $callable = [$this, $method->getName()];
                    $this->dependencies->setFactory($type_name, $callable);
                }
            }
        }
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
     * Get the session service.
     */
    public function smtp(): SmtpServiceInterface
    {
        return $this->getService(PhpMailerSmtpService::class);
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

        // Create and cache the instance.
        $service = $this->resolver->instantiate($class_name);
        $this->instances[$class_name] = $service;

        return $service;
    }
}
