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

use Laucov\WebFramework\Http\OutgoingResponse;
use Laucov\WebFramework\Http\RequestInterface;
use Laucov\WebFramework\Http\ResponseInterface;
use Laucov\WebFramework\Http\RouteClosure;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Http\RouteClosure
 */
class RouteClosureTest extends TestCase
{
    /**
     * @covers ::__construct
     * @uses Laucov\WebFramework\Http\RouteClosure::findClosureParameterTypes
     * @uses Laucov\WebFramework\Http\RouteClosure::findClosureReturnType
     */
    public function testCanAccessClosureAndParameterTypesAndReturnType(): void
    {
        $closure = function (string $a, string ...$b): string {
            return sprintf('Hello, %s and %s!', $a, implode(', ', $b));
        };
        $object = new RouteClosure($closure);

        $this->assertSame('string', $object->parameterTypes[0]->name);
        $this->assertSame(false, $object->parameterTypes[0]->isVariadic);
        $this->assertSame('string', $object->parameterTypes[1]->name);
        $this->assertSame(true, $object->parameterTypes[1]->isVariadic);
        $this->assertSame('string', $object->returnType);
        $this->assertSame($closure, $object->closure);
    }

    /**
     * @covers ::__construct
     * @covers ::findClosureParameterTypes
     * @uses Laucov\WebFramework\Http\RouteClosure::findClosureReturnType
     */
    public function testClosureMustNotReceiveUnionOrIntersectionTypes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RouteClosure(function (RequestInterface|string $a): string {
            return 'Hello, World!';
        });
    }

    /**
     * @covers ::__construct
     * @covers ::findClosureReturnType
     * @uses Laucov\WebFramework\Http\RouteClosure::findClosureParameterTypes
     */
    public function testClosureMustNotReturnUnionOrIntersectionTypes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RouteClosure(fn (): string|\Stringable => 'Hello, World!');
    }

    /**
     * @covers ::__construct
     * @covers ::findClosureParameterTypes
     * @uses Laucov\WebFramework\Http\RouteClosure::findClosureReturnType
     */
    public function testClosureMustReceiveStringsOrRequests(): void
    {
        // Test valid closures.
        new RouteClosure(function (RequestInterface $a, string $b): string {
            return 'Hello, World!';
        });
        new RouteClosure(function (string $a, string ...$b): string {
            return 'Hello, World!';
        });

        // Test invalid closure.
        $this->expectException(\InvalidArgumentException::class);
        new RouteClosure(function (string $a, \Stringable $b): string {
            return 'Hello, World!';
        });
    }

    /**
     * @covers ::__construct
     * @covers ::findClosureReturnType
     * @uses Laucov\WebFramework\Http\RouteClosure::findClosureParameterTypes
     */
    public function testClosureMustReturnStringOrStringableOrResponse(): void
    {
        // Create closure with Stringable return type.
        $closure_a = function (): \Stringable
        {
            return new class implements \Stringable
            {
                public function __toString(): string
                {
                    return 'Hello, World!';
                }
            };
        };

        // Create closure with ResponseInterface return type.
        $closure_b = function (): ResponseInterface
        {
            $response = new OutgoingResponse();
            return $response->setBody('Hello, World!');
        };

        // Create closure with string return type.
        $closure_c = fn (): string => 'Hello, World!';

        // Test adequate return types.
        new RouteClosure($closure_a);
        new RouteClosure($closure_b);
        new RouteClosure($closure_c);

        // Create closure with array return type.
        $closure_d = fn (): array => ['Hello,', 'World!'];

        // Test inadequate return type.
        $this->expectException(\InvalidArgumentException::class);
        new RouteClosure($closure_d);
    }
}
