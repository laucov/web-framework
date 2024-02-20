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

namespace Laucov\WebFramework\Database;
use Laucov\Db\Data\Connection;
use Laucov\Db\Data\Driver\DriverFactory;

/**
 * Stores database connection configurations and provides connection instances.
 */
class ConnectionProvider
{
    /**
     * Stored configurations.
     * 
     * @var array<string, array>
     */
    protected array $configurations = [];

    /**
     * Stored connection instances.
     * 
     * @var array<string, Connection>
     */
    protected array $instances = [];
    
    /**
     * Create the provider instance.
     */
    public function __construct(
        /**
         * Driver factory.
         */
        protected DriverFactory $driverFactory,
    ) {}

    /**
     * Get a connection instance using a stored configuration.
     */
    public function getConnection(string $name, bool $cache = true): Connection
    {
        if (!$cache) {
            return $this->createConnection($name);
        }

        if (array_key_exists($name, $this->instances)) {
            return $this->instances[$name];
        }

        $connection = $this->createConnection($name);
        $this->instances[$name] = $connection;

        return $connection;
    }

    /**
     * Store a new configuration for connection instantiation.
     */
    public function setConfiguration(
        string $name,
        string $dsn,
        null|string $username = null,
        null|string $password = null,
        null|array $options = null,
    ): static {
        $this->configurations[$name] = [
            'dsn' => $dsn,
            'username' => $username,
            'password' => $password,
            'options' => $options,
        ];

        return $this;
    }

    /**
     * Create a new connection instance.
     */
    protected function createConnection(string $name): Connection
    {
        $configuration = $this->configurations[$name];
        return new Connection($this->driverFactory, ...$configuration);
    }
}
