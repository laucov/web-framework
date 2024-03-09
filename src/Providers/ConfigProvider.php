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
     * @var array<string, class-string<T>>
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

        // Create name.
        $name = $this->getName($class_name);

        // Check if is already registered.
        if (array_key_exists($name, $this->classes)) {
            $msg = 'Configuration "%s" is already registered. Cannot add %s.';
            throw new \RuntimeException(sprintf($msg, $name, $class_name));
        }

        // Register.
        $this->classes[$name] = $class_name;

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
        // Get name.
        $name = $this->getName($class_name);

        // Check if the class name is registered.
        if (!array_key_exists($name, $this->classes)) {
            $msg = 'There is no configuration registered for %s.';
            throw new \InvalidArgumentException(sprintf($msg, $class_name));
        }

        // Get instance.
        $config = $this->getInstance($name);

        // Check class compatibility.
        if (!is_a($config, $class_name)) {
            $msg = 'The registered configuration class %s does not inherit or'
                . 'implement the requested class/interface %s.';
            throw new \RuntimeException(
                sprintf($msg, $config::class, $class_name),
            );
        }

        return $config;
    }

    /**
     * Apply environment variables to a configuration object.
     * 
     * @template T of ConfigInterface
     * @param class-string<T> $class_name
     * @return T
     */
    public function createInstance(string $class_name)
    {
        // Create instance.
        $instance = new $class_name();

        // Get environment name matches.
        $reflection = new \ReflectionObject($instance);
        /** @var array<\ReflectionAttribute> */
        $attributes = $reflection->getAttributes(EnvMatch::class);

        // Apply environment variables.
        foreach ($attributes as $attribute) {
            // Get variable name.
            /** @var EnvMatch */
            $env_match = $attribute->newInstance();
            $key = $env_match->variableName;
            // Check if value exists and use it.
            if (array_key_exists($key, $this->environment)) {
                $name = $env_match->propertyName;
                $instance->$name = $this->environment[$key];
            }
        }

        return $instance;
    }

    /**
     * Try to get a cached instance.
     */
    protected function getInstance(string $name): ConfigInterface
    {
        // Get class name.
        $class_name = $this->classes[$name];

        // Create if not cached.
        if (!array_key_exists($class_name, $this->instances)) {
            $this->instances[$class_name] = $this->createInstance($class_name);
        }

        return $this->instances[$class_name];
    }

    /**
     * Get the config name.
     * 
     * Removes the namespace from the class name.
     * 
     * Ex.: `'Foo\Bar\Baz'` => `'Baz'`
     */
    public function getName(string $class_name): string
    {
        // Get namespace separator position.
        $position = strrpos($class_name, '\\');

        return $position !== false
            ? substr($class_name, $position + 1)
            : $class_name;
    }
}
