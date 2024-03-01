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

declare(strict_types=1);

namespace Tests\Services;

use Laucov\Db\Data\Connection;
use Laucov\Db\Query\Schema;
use Laucov\Db\Query\Table;
use Laucov\WebFramework\Config\Database;
use Laucov\WebFramework\Services\DatabaseService;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Services\DatabaseService
 */
class DatabaseServiceTest extends TestCase
{
    /**
     * Configuration instance.
     */
    protected Database $config;

    /**
     * Service instance.
     */
    protected DatabaseService $service;

    /**
     * @covers ::__construct
     * @covers ::createConnection
     * @covers ::getConnection
     * @covers ::getSchema
     * @covers ::getTable
     * @uses Laucov\WebFramework\Providers\AbstractService::__construct
     * @uses Laucov\WebFramework\Providers\ConfigProvider::__construct
     */
    public function testCanSetAndGetConnections(): void
    {
        // Get connection.
        $conn_a = $this->service->getConnection('sqlite-a');
        $this->assertInstanceOf(Connection::class, $conn_a);

        // Test caching.
        $conn_b = $this->service->getConnection('sqlite-a');
        $this->assertSame($conn_a, $conn_b);
        $conn_c = $this->service->getConnection('sqlite-b');
        $this->assertNotSame($conn_a, $conn_c);

        // Get default connection.
        $conn_d = $this->service->getConnection();
        $this->assertSame($conn_a, $conn_d);

        // Get table instances.
        $table_a = $this->service->getTable('flights');
        $this->assertInstanceOf(Table::class, $table_a);
        $reflection = new \ReflectionObject($table_a);
        $this->assertSame(
            'flights',
            $reflection->getProperty('tableName')->getValue($table_a),
        );
        $this->assertSame(
            $conn_a,
            $reflection->getProperty('connection')->getValue($table_a),
        );
        $table_b = $this->service->getTable('flights');
        $this->assertNotSame($table_a, $table_b);

        // Get table with custom connection.
        $table_c = $this->service->getTable('airports', 'sqlite-b');
        $reflection = new \ReflectionObject($table_c);
        $this->assertSame(
            'airports',
            $reflection->getProperty('tableName')->getValue($table_c),
        );
        $this->assertSame(
            $conn_c,
            $reflection->getProperty('connection')->getValue($table_c),
        );

        // Get schema instances.
        $schema_a = $this->service->getSchema();
        $this->assertInstanceOf(Schema::class, $schema_a);
        $reflection = new \ReflectionObject($schema_a);
        $this->assertSame(
            $conn_a,
            $reflection->getProperty('connection')->getValue($schema_a),
        );
        $schema_b = $this->service->getSchema();
        $this->assertNotSame($schema_a, $schema_b);

        // Get schema with custom connection.
        $schema_c = $this->service->getSchema('sqlite-b');
        $reflection = new \ReflectionObject($schema_c);
        $this->assertSame(
            $conn_c,
            $reflection->getProperty('connection')->getValue($schema_c),
        );
    }

    protected function setUp(): void
    {
        // Create configuration.
        $this->config = new class extends Database {};
        $this->config->defaultConnections['sqlite-a'] = ['sqlite::memory:'];
        $this->config->defaultConnections['sqlite-b'] = ['sqlite::memory:'];
        $this->config->defaultConnection = 'sqlite-a';

        // Instantiate.
        $this->service = new DatabaseService($this->config);
    }
}
