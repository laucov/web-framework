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

namespace Laucov\WebFramework\Services;

use Laucov\Db\Data\Connection;
use Laucov\Db\Data\Driver\DriverFactory;
use Laucov\Db\Query\Schema;
use Laucov\Db\Query\Table;
use Laucov\WebFramework\Config\Database;
use Laucov\WebFramework\Providers\AbstractService;
use Laucov\WebFramework\Services\Interfaces\ServiceInterface;

/**
 * Provides an interface to configurable database connections and libraries.
 * 
 * @extends AbstractService<Database>
 */
class DatabaseService implements ServiceInterface
{
    /**
     * Cached connections.
     * 
     * @var array<string, Connection>
     */
    protected array $connections = [];

    /**
     * Driver factory.
     */
    protected DriverFactory $driverFactory;

    /**
     * Create the database service instance.
     */
    public function __construct(
        /**
         * Database configuration.
         */
        protected Database $config,
    ) {
        $factory_name = $config->driverFactoryName;
        $this->driverFactory = new $factory_name();
    }

    /**
     * Get a connection instance.
     */
    public function getConnection(null|string $name = null): Connection
    {
        $name ??= $this->config->defaultConnection;
        $this->connections[$name] ??= $this->createConnection($name);

        return $this->connections[$name];
    }

    /**
     * Get a schema instance.
     */
    public function getSchema(null|string $connection = null): Schema
    {
        return new Schema($this->getConnection($connection));
    }

    /**
     * Get a table instance.
     */
    public function getTable(
        string $name,
        null|string $connection = null,
    ): Table {
        return new Table($this->getConnection($connection), $name);
    }

    /**
     * Create a connection instance.
     */
    public function createConnection(string $name): Connection
    {
        $arguments = $this->config->defaultConnections[$name];
        $connection = new Connection($this->driverFactory, ...$arguments);

        return $connection;
    }
}
