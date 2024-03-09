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
use Laucov\WebFramework\Providers\ConfigInterface;
use Laucov\WebFramework\Providers\ConfigProvider;
use Laucov\WebFramework\Providers\ServiceProvider;
use Laucov\WebFramework\Services\DatabaseService;
use Laucov\WebFramework\Services\Interfaces\ServiceInterface;
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

    public function callProvider(): array
    {
        return [
            ['db', DatabaseService::class, [Database::class]],
            ['lang', LanguageService::class, [Language::class]],
            ['view', ViewService::class, [View::class]],
        ];
    }

    public function invalidChildrenProvider(): array
    {
        $config = new ConfigProvider([]);

        return [
            // Use a service with untyped constructor argument.
            [new class ($config) extends AbstractFooProvider
            {
                public function foobar()
                {
                    $this->getService(InvalidServiceA::class);
                }
            }],
            // Use a service with union/intersection constructor argument.
            [new class ($config) extends AbstractFooProvider
            {
                public function foobar()
                {
                    $this->getService(InvalidServiceB::class);
                }
            }],
            // Use a service with invalid type argument.
            [new class ($config) extends AbstractFooProvider
            {
                public function foobar()
                {
                    $this->getService(InvalidServiceC::class);
                }
            }],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::db
     * @covers ::getService
     * @covers ::lang
     * @covers ::view
     * @uses Laucov\WebFramework\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFramework\Providers\ConfigProvider::addConfig
     * @uses Laucov\WebFramework\Providers\ConfigProvider::createInstance
     * @uses Laucov\WebFramework\Providers\ConfigProvider::getConfig
     * @uses Laucov\WebFramework\Providers\ConfigProvider::getName
     * @uses Laucov\WebFramework\Providers\ConfigProvider::getInstance
     * @uses Laucov\WebFramework\Services\DatabaseService::__construct
     * @uses Laucov\WebFramework\Services\LanguageService::__construct
     * @uses Laucov\WebFramework\Services\LanguageService::update
     * @uses Laucov\WebFramework\Services\ViewService::__construct
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
     * @uses Laucov\WebFramework\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFramework\Providers\ServiceProvider::__construct
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
    public function __construct($a)
    {}
}

class InvalidServiceB implements ServiceInterface
{
    public function __construct(ConfigInterface|ServiceInterface $a)
    {}
}

class InvalidServiceC implements ServiceInterface
{
    public function __construct(array $b)
    {}
}
