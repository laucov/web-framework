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

namespace Tests\Security;

use Laucov\WebFramework\Providers\ConfigProvider;
use Laucov\WebFramework\Providers\ServiceProvider;
use Laucov\WebFramework\Security\Authentication\AuthnFactory;
// use Laucov\WebFramework\Security\Authentication\Interfaces\AuthnInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Security\Authentication\AuthnFactory
 * @todo Create `AuthnInterface` classes to test.
 */
class AuthnFactoryTest extends TestCase
{
    // protected AuthnFactory $factory;

    // public function authnNameProvider(): array
    // {
    //     return [
    //     ];
    // }

    /**
     * @covers ::__construct
     * @uses Laucov\WebFramework\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFramework\Providers\ServiceDependencyRepository::setConfigProvider
     * @uses Laucov\WebFramework\Providers\ServiceProvider::__construct
     */
    public function testCanInstantiate(): void
    {
        $this->expectNotToPerformAssertions();
        $config = new ConfigProvider([]);
        $services = new ServiceProvider($config);
        new AuthnFactory($services);
    }

    // /**
    //  * @covers ::__construct
    //  * @dataProvider authnNameProvider
    //  */
    // public function testCanGetAuthnObjs(string $name, string $expected): void
    // {
    //     $instance = $this->factory->{$name}();
    //     $this->assertInstanceOf(AuthnInterface::class, $instance);
    //     $this->assertInstanceOf($expected, $instance);
    // }

    // protected function setUp(): void
    // {
    //     $config = new ConfigProvider([]);
    //     $services = new ServiceProvider($config);
    //     $this->factory = new AuthnFactory($services);
    // }
}
