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

use Laucov\WebFwk\Config\Database;
use Laucov\WebFwk\Config\Language;
use Laucov\WebFwk\Config\Session;
use Laucov\WebFwk\Config\View;
use Laucov\WebFwk\Config\Interfaces\ConfigInterface;
use Laucov\WebFwk\Providers\ConfigProvider;
use Laucov\WebFwk\Providers\ServiceProvider;
use Laucov\WebFwk\Services\DatabaseService;
use Laucov\WebFwk\Services\Interfaces\ServiceInterface;
use Laucov\WebFwk\Services\Interfaces\SessionServiceInterface;
use Laucov\WebFwk\Services\LanguageService;
use Laucov\WebFwk\Services\ViewService;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Providers\ServiceProvider
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

    public function callProvider(): array
    {
        return [
            ['db', DatabaseService::class, [Database::class]],
            ['lang', LanguageService::class, [Language::class]],
            ['session', SessionServiceInterface::class, [Session::class]],
            ['view', ViewService::class, [View::class]],
        ];
    }

    public function invalidChildrenProvider(): array
    {
        $config = new ConfigProvider([]);

        return [
            // Use a service with union/intersection constructor argument.
            [new class ($config) extends AbstractFooProvider {
                public function foobar()
                {
                    $this->getService(InvalidServiceA::class);
                }
            }],
            // Use a service with invalid type argument.
            [new class ($config) extends AbstractFooProvider {
                public function foobar()
                {
                    $this->getService(InvalidServiceB::class);
                }
            }],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::db
     * @covers ::getService
     * @covers ::lang
     * @covers ::session
     * @covers ::view
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ConfigProvider::addConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::createInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getName
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::hasConfig
     * @uses Laucov\WebFwk\Providers\EnvMatch::__construct
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::getValue
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::hasDependency
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::setConfigProvider
     * @uses Laucov\WebFwk\Services\DatabaseService::__construct
     * @uses Laucov\WebFwk\Services\FileSessionService::__construct
     * @uses Laucov\WebFwk\Services\LanguageService::__construct
     * @uses Laucov\WebFwk\Services\LanguageService::update
     * @uses Laucov\WebFwk\Services\ViewService::__construct
     * @dataProvider callProvider
     */
    public function testCanGetServices(
        string $method_name,
        string $service_class_name,
        array $config_classes,
    ): void {
        // Add configuration classes.
        foreach ($config_classes as $class_name) {
            $this->config->addConfig($class_name);
        }

        // Get a new instance.
        $a = $this->services->{$method_name}();
        $this->assertInstanceOf($service_class_name, $a);

        // Get cached instance.
        $b = $this->services->{$method_name}();
        $this->assertSame($a, $b);
    }

    /**
     * @covers ::getService
     * @dataProvider invalidChildrenProvider
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::hasDependency
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::setConfigProvider
     * @uses Laucov\WebFwk\Providers\ServiceProvider::__construct
     */
    public function testValidatesServiceConstructors(object $provider): void
    {
        $this->assertInstanceOf(AbstractFooProvider::class, $provider);
        $this->expectException(\RuntimeException::class);
        $provider->foobar();
    }

    protected function setUp(): void
    {
        $this->config = new ConfigProvider([]);
        $this->services = new ServiceProvider($this->config);
    }
}

abstract class AbstractFooProvider extends ServiceProvider
{
    abstract public function foobar();
}

class InvalidServiceA implements ServiceInterface
{
    public function __construct(ConfigInterface|ServiceInterface $a)
    {
    }
}

class InvalidServiceB implements ServiceInterface
{
    public function __construct(array $b)
    {
    }
}
