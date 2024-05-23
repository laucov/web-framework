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

use Laucov\Sessions\Session;
use Laucov\WebFwk\Config\Database;
use Laucov\WebFwk\Config\Language;
use Laucov\WebFwk\Config\Session as SessionCfg;
use Laucov\WebFwk\Config\View;
use Laucov\WebFwk\Config\Interfaces\ConfigInterface;
use Laucov\WebFwk\Config\Smtp;
use Laucov\WebFwk\Providers\ConfigProvider;
use Laucov\WebFwk\Providers\ServiceProvider;
use Laucov\WebFwk\Services\DatabaseService;
use Laucov\WebFwk\Services\FileSessionService;
use Laucov\WebFwk\Services\Interfaces\ServiceInterface;
use Laucov\WebFwk\Services\Interfaces\SessionServiceInterface;
use Laucov\WebFwk\Services\Interfaces\SmtpServiceInterface;
use Laucov\WebFwk\Services\LanguageService;
use Laucov\WebFwk\Services\PhpMailerSmtpService;
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

    /**
     * Provides a service provider that provides invalid service classes.
     * 
     * Also provides method names to test each of the provider methods.
     */
    public function invalidServiceProviderProvider(): array
    {
        // Create generic config provider.
        $config = new ConfigProvider([]);

        // Create provider with invalid services.
        $services = new class ($config) extends ServiceProvider {
            public function a(): InvalidServiceA
            {
                return $this->getService(InvalidServiceA::class);
            }
            public function b(): InvalidServiceB
            {
                return $this->getService(InvalidServiceB::class);
            }
        };

        return [
            // Test method for service with union type constructor argument.
            [$services, 'a'],
            // Test method for service with unresolvable argument type.
            [$services, 'b'],
        ];
    }

    /**
     * Provides test parameters for service method calls.
     */
    public function serviceCallProvider(): array
    {
        return [
            // Arguments:
            // - Expected class name;
            // - Provider method to call;
            // - Configuration classes to setup before requesting the service.
            [DatabaseService::class, 'db', [Database::class]],
            [LanguageService::class, 'lang', [Language::class]],
            [SessionServiceInterface::class, 'session', [SessionCfg::class]],
            [SmtpServiceInterface::class, 'smtp', [Smtp::class]],
            [ViewService::class, 'view', [View::class]],
        ];
    }

    /**
     * @coversNothing
     */
    public function testCanChooseSessionServiceClass(): void
    {
        // Test session service.
        $this->config->addConfig(SessionCfg::class);
        $mock = $this->createMock(SessionServiceInterface::class);
        $config = $this->config->getConfig(SessionCfg::class);
        $default_class = $config->service;
        $config->service = $mock::class;
        $service = $this->services->session();
        $this->assertNotInstanceOf($default_class, $service);
        $this->assertInstanceOf($mock::class, $service);
    }

    /**
     * @coversNothing
     */
    public function testCanChooseSmtpServiceClass(): void
    {
        // Test SMTP service.
        $mock = $this->createMock(SmtpServiceInterface::class);
        $this->config->addConfig(Smtp::class);
        $config = $this->config->getConfig(Smtp::class);
        $default_class = $config->service;
        $config->service = $mock::class;
        $service = $this->services->smtp();
        $this->assertNotInstanceOf($default_class, $service);
        $this->assertInstanceOf($mock::class, $service);
    }

    /**
     * @covers ::__construct
     * @covers ::db
     * @covers ::getService
     * @covers ::lang
     * @covers ::session
     * @covers ::smtp
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
     * @uses Laucov\WebFwk\Services\PhpMailerSmtpService::__construct
     * @uses Laucov\WebFwk\Services\ViewService::__construct
     * @dataProvider serviceCallProvider
     */
    public function testCanGetServices(
        string $expected_class,
        string $method_name,
        array $required_config,
    ): void {
        // Add configuration classes.
        foreach ($required_config as $class_name) {
            $this->config->addConfig($class_name);
        }

        // Get a new instance.
        $a = $this->services->{$method_name}();
        $this->assertInstanceOf($expected_class, $a);

        // Get cached instance.
        $b = $this->services->{$method_name}();
        $this->assertSame($a, $b);
    }

    /**
     * @covers ::getService
     * @dataProvider invalidServiceProviderProvider
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::hasDependency
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::setConfigProvider
     * @uses Laucov\WebFwk\Providers\ServiceProvider::__construct
     */
    public function testValidatesServiceConstructors(
        ServiceProvider $provider,
        string $method_name,
    ): void {
        $this->expectException(\RuntimeException::class);
        $provider->{$method_name}();
    }

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->config = new ConfigProvider([]);
        $this->services = new ServiceProvider($this->config);
    }
}

/**
 * Invalid service example.
 * 
 * Contains union types in the constructor.
 */
class InvalidServiceA implements ServiceInterface
{
    public function __construct(ConfigInterface|ServiceInterface $arg)
    {
    }
}

/**
 * Invalid service example.
 * 
 * Contains a constructor argument type that can't be resolved.
 */
class InvalidServiceB implements ServiceInterface
{
    public function __construct(array $arg)
    {
    }
}
