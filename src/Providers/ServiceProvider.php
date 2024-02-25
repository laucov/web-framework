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
use Laucov\WebFramework\Services\DatabaseService;

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
     * Get a database service.
     */
    public function db(): DatabaseService
    {
        if (!array_key_exists('db', $this->instances)) {
            $config = $this->config->getConfig(Database::class);
            $this->instances['db'] = new DatabaseService($config);
        }

        return $this->instances['db'];
    }
}
