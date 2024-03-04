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

use Laucov\WebFramework\Config\Database;
use Laucov\WebFramework\Config\Language;
use Laucov\WebFramework\Services\DatabaseService;
use Laucov\WebFramework\Services\LanguageService;

/**
 * Caches and provides service object instances.
 * 
 * @template T of AbstractService
 */
class ServiceProvider
{
    /**
     * Cached instances.
     * 
     * @var array<string, T>
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
        return $this->getService(
            'db',
            DatabaseService::class,
            Database::class,
        );
    }

    /**
     * Get the language service.
     */
    public function lang(): LanguageService
    {
        return $this->getService(
            'lang',
            LanguageService::class,
            Language::class,
        );
    }

    /**
     * Cache a new service instance.
     * 
     * @param string $name
     * @param class-string<T> $service
     * @param class-string<ConfigInterface> $config
     * @return T
     */
    protected function getService(
        string $name,
        string $service,
        string $config,
    ): mixed {
        if (array_key_exists($name, $this->instances)) {
            return $this->instances[$name];
        }

        $config = $this->config->getConfig($config);
        $service = new $service($config);

        $this->instances[$name] = $service;

        return $service;
    }
}
