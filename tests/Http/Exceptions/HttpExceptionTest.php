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

namespace Tests\Http\Exceptions;

use Laucov\WebFwk\Http\Exceptions\HttpException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Http\Exceptions\HttpException
 */
class HttpExceptionTest extends TestCase
{
    /**
     * Provides invalid status codes.
     */
    public function invalidStatusCodeProvider(): array
    {
        return [[-100], [99], [600], [1400]];
    }

    /**
     * @covers ::__construct
     */
    public function testDefaultsToBadRequest(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(400);
        throw new HttpException();
    }

    /**
     * @covers ::__construct
     * @dataProvider invalidStatusCodeProvider
     */
    public function testValidatesStatusCodes(int $code): void
    {
        $this->expectException(\InvalidArgumentException::class);
        throw new HttpException('', $code);
    }
}
