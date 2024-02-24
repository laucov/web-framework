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

/**
 * Caches and provides configuration object instances.
 * 
 * @template T of ConfigInterface
 */
class ConfigProvider
{
    /**
     * Registered classes.
     * 
     * @var array<class-string<T>>
     */
    protected array $classes = [];

    /**
     * Currently cached instances.
     * 
     * @var array<class-string<T>, T>
     */
    protected array $instances = [];

    /**
     * Create the provider instance.
     */
    public function __construct(
        /**
         * Environment variables.
         */
        protected array $environment,
    ) {
    }

    /**
     * Add a configuration class.
     * 
     * @param class-string<ConfigInterface> $class_name
     */
    public function addConfig(string $class_name): static
    {
        // Check if the class implements ConfigInterface.
        if (!is_a($class_name, ConfigInterface::class, true)) {
            $msg = 'All configuration classes must implement %s.';
            $intf_name = ConfigInterface::class;
            throw new \InvalidArgumentException(sprintf($msg, $intf_name));
        }

        // Check if is already registered.
        if (in_array($class_name, $this->classes, true)) {
            $msg = 'Configuration %s is already registered.';
            throw new \RuntimeException(sprintf($msg, $class_name));
        }

        // Register.
        $this->classes[] = $class_name;

        return $this;
    }

    /**
     * Get a configuration instance.
     * 
     * @param class-string<T>
     * @return T
     */
    public function getConfig(string $class_name): mixed
    {
        // Check if the class name is registered.
        if (!in_array($class_name, $this->classes, true)) {
            $msg = 'There is no configuration registered for %s.';
            throw new \InvalidArgumentException(sprintf($msg, $class_name));
        }

        return $this->getOrCacheInstance($class_name);
    }

    /**
     * Try to get a cached instance.
     * 
     * @param class-string<T>
     * 
     * @var T
     */
    public function getOrCacheInstance(string $class_name)
    {
        if (!array_key_exists($class_name, $this->instances)) {
            $instance = new $class_name();
            $this->applyEnvironmentValues($instance);
            $this->instances[$class_name] = $instance;
        }

        return $this->instances[$class_name];
    }

    /**
     * Apply environment variables to a configuration object.
     */
    public function applyEnvironmentValues(object $object): void
    {
        // Get attributes.
        $reflection = new \ReflectionObject($object);
        /** @var array<\ReflectionAttribute> */
        $attributes = $reflection->getAttributes(EnvMatch::class);

        // Apply variables.
        foreach ($attributes as $attribute) {
            // Get match.
            /** @var EnvMatch */
            $env_match = $attribute->newInstance();
            $key = $env_match->variableName;
            // Check if environment value exists and use it.
            if (array_key_exists($key, $this->environment)) {
                // Replace default value.
                $value = $this->environment[$key];
                $object->{$env_match->propertyName} = $value;
            }
        }
    }
}
