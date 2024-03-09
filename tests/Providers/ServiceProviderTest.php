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
use Laucov\WebFramework\Config\Language;
use Laucov\WebFramework\Config\View;
use Laucov\WebFramework\Providers\AbstractService;
use Laucov\WebFramework\Providers\ConfigProvider;
use Laucov\WebFramework\Providers\ServiceProvider;
use Laucov\WebFramework\Services\DatabaseService;
use Laucov\WebFramework\Services\LanguageService;
use Laucov\WebFramework\Services\ViewService;
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
     * @covers ::__construct
     * @covers ::db
     * @covers ::getService
     * @covers ::lang
     * @covers ::view
     * @uses Laucov\WebFramework\Providers\AbstractService::__construct
     * @uses Laucov\WebFramework\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFramework\Providers\ConfigProvider::addConfig
     * @uses Laucov\WebFramework\Providers\ConfigProvider::applyEnvironmentValues
     * @uses Laucov\WebFramework\Providers\ConfigProvider::getConfig
     * @uses Laucov\WebFramework\Providers\ConfigProvider::getConfigName
     * @uses Laucov\WebFramework\Providers\ConfigProvider::getOrCacheInstance
     * @uses Laucov\WebFramework\Services\DatabaseService::__construct
     * @uses Laucov\WebFramework\Services\LanguageService::__construct
     * @uses Laucov\WebFramework\Services\LanguageService::update
     * @uses Laucov\WebFramework\Services\ViewService::__construct
     */
    public function testCanGetServices(): void
    {
        // Set service list.
        $services = [
            ['db', DatabaseService::class, Database::class],
            ['lang', LanguageService::class, Language::class],
            ['view', ViewService::class, View::class],
        ];

        // Test each service.
        foreach ($services as [$method, $service, $config]) {
            // Add config.
            $this->config->addConfig($config);
            // Get a new instance.
            $a = $this->services->{$method}();
            $this->assertInstanceOf($service, $a);
            // Get cached instance.
            $b = $this->services->{$method}();
            $this->assertSame($a, $b);
        }
    }

    protected function setUp(): void
    {
        $this->config = new ConfigProvider([]);
        $this->services = new ServiceProvider($this->config);
    }
}
