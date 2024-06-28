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

namespace Tests\Unit\Cli;

use Laucov\Cli\AbstractRequest;
use Laucov\Cli\Interfaces\CommandInterface;
use Laucov\WebFwk\Cli\CommandRouter;
use Laucov\WebFwk\Providers\ConfigProvider;
use Laucov\WebFwk\Providers\ServiceProvider;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Cli\CommandRouter
 */
class CommandRouterTest extends TestCase
{
    /**
     * Router instance.
     */
    protected CommandRouter $router;

    /**
     * @covers ::setProviders
     */
    public function testCanSetProvidersAsDependencies(): void
    {
        // Mock providers.
        $config = $this->createMock(ConfigProvider::class);
        $services = $this->createMock(ServiceProvider::class);
        $request = $this->createMock(AbstractRequest::class);
        $request
            ->method('getCommand')
            ->willReturn('do-something');

        // Create command.
        $command = new class (
            $request,
            $config,
            $services
        ) implements CommandInterface {
            public function __construct(
                public AbstractRequest $request,
                public ConfigProvider $config,
                public ServiceProvider $services,
            ) {
            }
            public function run(): void
            {
            }
        };

        // Set providers, add command and route.
        $result = $this->router
            ->setProviders($config, $services)
            ->addCommand('do-something', $command::class)
            ->route($request);
        $this->assertInstanceOf($command::class, $result);
        $this->assertSame($request, $result->request);
        $this->assertSame($config, $result->config);
        $this->assertSame($services, $result->services);
    }

    /**
     * This method is called before each test.
     */
    public function setUp(): void
    {
        $this->router = new CommandRouter();
    }
}
