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

namespace Tests\Http;

use Laucov\Db\Data\Driver\DriverFactory;
use Laucov\WebFramework\Database\ConnectionProvider;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Database\ConnectionProvider
 */
class ConnectionProviderTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::createConnection
     * @covers ::getConnection
     * @covers ::setConfiguration
     */
    public function testCanSetConfigurationAndGetConnection(): void
    {
        // Create provider instance.
        $factory = new DriverFactory();
        $provider = new ConnectionProvider($factory);

        // Add configurations.
        $provider
            ->setConfiguration('config_a', 'sqlite::memory:')
            ->setConfiguration('config_b', 'sqlite::memory:');

        // Get instances.
        $conn_a = $provider->getConnection('config_a');
        $conn_b = $provider->getConnection('config_b');
        $this->assertNotSame($conn_a, $conn_b);
        $conn_c = $provider->getConnection('config_a');
        $this->assertSame($conn_a, $conn_c);
        $conn_d = $provider->getConnection('config_a', false);
        $this->assertNotSame($conn_a, $conn_d);
    }
}
