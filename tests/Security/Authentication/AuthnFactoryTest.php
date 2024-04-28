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

use Laucov\WebFwk\Providers\ConfigProvider;
use Laucov\WebFwk\Providers\ServiceProvider;
use Laucov\WebFwk\Security\Authentication\AuthnFactory;
use Laucov\WebFwk\Security\Authentication\Interfaces\AuthnInterface;
use Laucov\WebFwk\Security\Authentication\TotpAuthn;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Security\Authentication\AuthnFactory
 */
class AuthnFactoryTest extends TestCase
{
    protected AuthnFactory $factory;

    public function authnOptionProvider(): array
    {
        return [
            ['totp', TotpAuthn::class],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::totp
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::setConfigProvider
     * @uses Laucov\WebFwk\Providers\ServiceProvider::__construct
     * @dataProvider authnOptionProvider
     */
    public function testCanGetAuthnObjs(string $name, string $expected): void
    {
        $instance = $this->factory->{$name}();
        $this->assertInstanceOf(AuthnInterface::class, $instance);
        $this->assertInstanceOf($expected, $instance);
    }

    protected function setUp(): void
    {
        $config = new ConfigProvider([]);
        $services = new ServiceProvider($config);
        $this->factory = new AuthnFactory($services);
    }
}
