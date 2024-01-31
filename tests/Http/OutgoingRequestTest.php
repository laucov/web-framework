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

use Laucov\Arrays\ArrayBuilder;
use Laucov\WebFramework\Http\OutgoingRequest;
use Laucov\WebFramework\Web\Uri;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Http\OutgoingRequest
 */
class OutgoingRequestTest extends TestCase
{
    protected OutgoingRequest $request;

    protected function setUp(): void
    {
        $this->request = new OutgoingRequest();
    }

    /**
     * @covers ::__construct
     * @covers ::getParameters
     * @uses Laucov\Arrays\ArrayBuilder::__construct
     * @uses Laucov\WebFramework\Http\OutgoingRequest::__construct
     */
    public function testCanGetParameters(): void
    {
        $parameters = $this->request->getParameters();
        $this->assertInstanceOf(ArrayBuilder::class, $parameters);
    }

    /**
     * @covers ::__construct
     * @covers ::getPostVariables
     * @uses Laucov\Arrays\ArrayBuilder::__construct
     * @uses Laucov\WebFramework\Http\OutgoingRequest::__construct
     */
    public function testCanGetPostVariables(): void
    {
        $variables = $this->request->getPostVariables();
        $this->assertInstanceOf(ArrayBuilder::class, $variables);
    }

    /**
     * @covers ::getMethod
     * @covers ::setMethod
     * @uses Laucov\Arrays\ArrayBuilder::__construct
     * @uses Laucov\WebFramework\Http\OutgoingRequest::__construct
     */
    public function testCanSetMethod(): void
    {
        $this->assertSame(
            $this->request,
            $this->request->setMethod('PUT'),
        );
        $this->assertSame('PUT', $this->request->getMethod());
        $this->request->setMethod('get');
        $this->assertSame('GET', $this->request->getMethod());
        $this->request->setMethod('PaTcH');
        $this->assertSame('PATCH', $this->request->getMethod());
    }

    /**
     * @covers ::getUri
     * @covers ::setUri
     * @uses Laucov\Arrays\ArrayBuilder::__construct
     * @uses Laucov\WebFramework\Http\OutgoingRequest::__construct
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testCanSetUri(): void
    {
        $uri = Uri::fromString('http://example.com');
        $this->assertSame(
            $this->request,
            $this->request->setUri($uri),
        );
        $this->assertSame($uri, $this->request->getUri());
    }
}
