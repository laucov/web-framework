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

use Laucov\WebFramework\Http\Route;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Http\Route
 */
class RouteTest extends TestCase
{
    protected Route $route;

    protected function setUp(): void
    {
        $this->route = new Route(fn () => "Hello, World!", ['$1', '$2']);
    }

    /**
     * @covers ::__construct
     */
    public function testCanGetProperties(): void
    {
        $this->assertIsCallable($this->route->callable);
        $this->assertIsArray($this->route->arguments);

        foreach ($this->route->arguments as $argument) {
            $this->assertIsString($argument);
        }
    }

    /**
     * @covers ::__construct
     */
    public function testMustUseStringArguments(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Route(fn () => null, ['foo', 1, fn () => "foo", [], null]);
    }
}
