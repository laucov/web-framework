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

namespace Tests\Providers;

use Laucov\WebFramework\Config\Database;
use Laucov\WebFramework\Providers\AbstractService;
use Laucov\WebFramework\Providers\ConfigProvider;
use Laucov\WebFramework\Providers\ServiceProvider;
use Laucov\WebFramework\Services\DatabaseService;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Providers\ServiceProvider
 */
class ServiceProviderTest extends TestCase
{
    /**
     * Configuration provider.
     */
    protected ConfigProvider $config;

    /**
     * Provider.
     */
    protected ServiceProvider $services;

    /**
     * @covers ::db
     * @uses Laucov\WebFramework\Providers\AbstractService::__construct
     * @uses Laucov\WebFramework\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFramework\Providers\ConfigProvider::addConfig
     * @uses Laucov\WebFramework\Providers\ConfigProvider::applyEnvironmentValues
     * @uses Laucov\WebFramework\Providers\ConfigProvider::getConfig
     * @uses Laucov\WebFramework\Providers\ConfigProvider::getOrCacheInstance
     * @uses Laucov\WebFramework\Providers\ServiceProvider::__construct
     * @uses Laucov\WebFramework\Services\DatabaseService::__construct
     */
    public function testCanGetServices(): void
    {
        // Try to get the database service.
        $this->config->addConfig(Database::class);
        $db_a = $this->services->db();
        $this->assertInstanceOf(DatabaseService::class, $db_a);
        // Test caching.
        $db_b = $this->services->db();
        $this->assertSame($db_a, $db_b);
    }

    protected function setUp(): void
    {
        $this->config = new ConfigProvider([]);
        $this->services = new ServiceProvider($this->config);
    }
}