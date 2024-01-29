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

use Laucov\WebFramework\Http\Exceptions\NotFoundException;
use Laucov\WebFramework\Http\IncomingRequest;
use Laucov\WebFramework\Http\OutgoingResponse;
use Laucov\WebFramework\Http\RequestInterface;
use Laucov\WebFramework\Http\ResponseInterface;
use Laucov\WebFramework\Http\Router;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Http\Router
 */
class RouterTest extends TestCase
{
    protected Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    /**
     * @covers ::__construct
     * @covers ::popPrefix
     * @covers ::pushPrefix
     * @covers ::setRoute
     * @uses Laucov\WebFramework\Data\ArrayBuilder::setValue
     * @uses Laucov\WebFramework\Data\ArrayReader::__construct
     * @uses Laucov\WebFramework\Data\ArrayReader::getArray
     * @uses Laucov\WebFramework\Data\ArrayReader::validateKeys
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Files\StringSource::__toString
     * @uses Laucov\WebFramework\Http\AbstractIncomingMessage::__construct
     * @uses Laucov\WebFramework\Http\AbstractMessage::getBody
     * @uses Laucov\WebFramework\Http\AbstractOutgoingMessage::setBody
     * @uses Laucov\WebFramework\Http\IncomingRequest::__construct
     * @uses Laucov\WebFramework\Http\Route::getParameters
     * @uses Laucov\WebFramework\Http\Router::findRoute
     * @uses Laucov\WebFramework\Http\Router::getClosureReturnTypes
     * @uses Laucov\WebFramework\Http\Router::getReflectionTypeNames
     * @uses Laucov\WebFramework\Http\Router::route
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testCanPrefix(): void
    {
        // Test pushing prefixes.
        $this->assertSame($this->router, $this->router->pushPrefix('prefix'));
        $this->router->setRoute('some-route', function (): string {
            return 'Some output.';
        });
        $this->router
            ->pushPrefix('foobar')
            ->setRoute('the-route', fn (): string => 'The output.');
        
        // Test popping prefixes.
        $this->assertSame($this->router, $this->router->popPrefix());
        $this->router->setRoute('another-route', function (): string {
            return 'Another output.';
        });
        $this->router
            ->popPrefix()
            ->setRoute('final-route', fn (): string => 'Final output.');

        // Test routes.
        $request_a = $this->getRequest('prefix/some-route');
        $response_a = $this->router->route($request_a);
        $this->assertSame('Some output.', (string) $response_a->getBody());
        $request_b = $this->getRequest('prefix/foobar/the-route');
        $response_b = $this->router->route($request_b);
        $this->assertSame('The output.', (string) $response_b->getBody());
        $request_c = $this->getRequest('prefix/another-route');
        $response_c = $this->router->route($request_c);
        $this->assertSame('Another output.', (string) $response_c->getBody());
        $request_d = $this->getRequest('final-route');
        $response_d = $this->router->route($request_d);
        $this->assertSame('Final output.', (string) $response_d->getBody());
    }

    /**
     * @covers ::__construct
     * @covers ::findRoute
     * @covers ::route
     * @covers ::setPattern
     * @uses Laucov\WebFramework\Data\ArrayBuilder::setValue
     * @uses Laucov\WebFramework\Data\ArrayReader::__construct
     * @uses Laucov\WebFramework\Data\ArrayReader::getArray
     * @uses Laucov\WebFramework\Data\ArrayReader::validateKeys
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Files\StringSource::__toString
     * @uses Laucov\WebFramework\Http\AbstractIncomingMessage::__construct
     * @uses Laucov\WebFramework\Http\AbstractMessage::getBody
     * @uses Laucov\WebFramework\Http\AbstractOutgoingMessage::setBody
     * @uses Laucov\WebFramework\Http\IncomingRequest::__construct
     * @uses Laucov\WebFramework\Http\Route::addParameter
     * @uses Laucov\WebFramework\Http\Route::getParameters
     * @uses Laucov\WebFramework\Http\Router::getClosureReturnTypes
     * @uses Laucov\WebFramework\Http\Router::getReflectionTypeNames
     * @uses Laucov\WebFramework\Http\Router::setRoute
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testCanSetPatterns(): void
    {
        // Add unused pattern.
        $result = $this->router->setPattern('alpha', '/^[A-Za-z]+$/');
        $this->assertSame($this->router, $result);

        // Add used pattern.
        $result = $this->router->setPattern('int', '/^[0-9]+$/');
        $this->assertSame($this->router, $result);

        // Test pattern.
        $closure = fn (string $int): string => 'Foobar #' . $int;
        $this->router->setRoute('foobars/:int', $closure);
        $response_a = $this->router->route($this->getRequest('foobars/8'));
        $this->assertSame('Foobar #8', (string) $response_a->getBody());
    }

    /**
     * @covers ::__construct
     * @covers ::setRoute
     * @covers ::getClosureReturnTypes
     * @covers ::getReflectionTypeNames
     * @covers ::route
     * @uses Laucov\WebFramework\Data\ArrayBuilder::setValue
     * @uses Laucov\WebFramework\Data\ArrayReader::__construct
     * @uses Laucov\WebFramework\Data\ArrayReader::getArray
     * @uses Laucov\WebFramework\Data\ArrayReader::validateKeys
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Files\StringSource::__toString
     * @uses Laucov\WebFramework\Http\AbstractIncomingMessage::__construct
     * @uses Laucov\WebFramework\Http\AbstractMessage::getBody
     * @uses Laucov\WebFramework\Http\AbstractOutgoingMessage::setBody
     * @uses Laucov\WebFramework\Http\IncomingRequest::__construct
     * @uses Laucov\WebFramework\Http\Route::getParameters
     * @uses Laucov\WebFramework\Http\Router::findRoute
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testClosureMustReturnResponseOrStringOrStringable(): void
    {
        // Create response route.
        $this->router->setRoute('route-a', function (): ResponseInterface {
            $response = new OutgoingResponse();
            return $response->setBody('This is a response.');
        });

        // Create string route.
        $this->router->setRoute('route-b', function (): string {
            return 'This is a response.';
        });

        // Create Stringable route.
        $this->router->setRoute('route-c', function (): \Stringable {
            return new class () implements \Stringable {
                public function __toString(): string
                {
                    return 'This is a response.';
                }
            };
        });

        // Check results.
        $expected = 'This is a response.';
        $response_a = $this->router->route($this->getRequest('route-a'));
        $this->assertSame($expected, (string) $response_a->getBody());
        $response_b = $this->router->route($this->getRequest('route-b'));
        $this->assertSame($expected, (string) $response_b->getBody());
        $response_c = $this->router->route($this->getRequest('route-c'));
        $this->assertSame($expected, (string) $response_c->getBody());

        // Test closure with union type.
        $this->router->setRoute('route-d', function (): string|\Stringable {
            return 'Testing with union types!';
        });

        // Test invalid closure.
        $this->expectException(\InvalidArgumentException::class);
        $this->router->setRoute('route-e', function (): string|array {
            return ['not', 'a', 'valid', 'result'];
        });
    }

    /**
     * @covers ::__construct
     * @covers ::findRoute
     * @covers ::route
     * @uses Laucov\WebFramework\Data\ArrayBuilder::setValue
     * @uses Laucov\WebFramework\Data\ArrayReader::__construct
     * @uses Laucov\WebFramework\Data\ArrayReader::getArray
     * @uses Laucov\WebFramework\Data\ArrayReader::validateKeys
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Http\AbstractIncomingMessage::__construct
     * @uses Laucov\WebFramework\Http\IncomingRequest::__construct
     * @uses Laucov\WebFramework\Http\Router::getClosureReturnTypes
     * @uses Laucov\WebFramework\Http\Router::getReflectionTypeNames
     * @uses Laucov\WebFramework\Http\Router::setRoute
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testDifferentiatesIntermediaryAndFinalSegments(): void
    {
        $this->router->setRoute('foo/bar', fn (): string => 'You found me!');
        $this->expectException(NotFoundException::class);
        $this->router->route($this->getRequest('foo'));
    }

    /**
     * @covers ::__construct
     * @covers ::route
     * @uses Laucov\WebFramework\Data\ArrayBuilder::setValue
     * @uses Laucov\WebFramework\Data\ArrayReader::__construct
     * @uses Laucov\WebFramework\Data\ArrayReader::getArray
     * @uses Laucov\WebFramework\Data\ArrayReader::validateKeys
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Files\StringSource::__toString
     * @uses Laucov\WebFramework\Http\AbstractIncomingMessage::__construct
     * @uses Laucov\WebFramework\Http\AbstractMessage::getBody
     * @uses Laucov\WebFramework\Http\AbstractOutgoingMessage::setBody
     * @uses Laucov\WebFramework\Http\IncomingRequest::__construct
     * @uses Laucov\WebFramework\Http\Route::getParameters
     * @uses Laucov\WebFramework\Http\Router::findRoute
     * @uses Laucov\WebFramework\Http\Router::setRoute
     * @uses Laucov\WebFramework\Http\Router::getClosureReturnTypes
     * @uses Laucov\WebFramework\Http\Router::getReflectionTypeNames
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testPassesDependencies(): void
    {
        // Add routes.
        $closure = fn (RequestInterface $request): string => 'Foo!';
        $this->router->setRoute('foo', $closure);

        // Test routes.
        $response_a = $this->router->route($this->getRequest('foo'));
        $this->assertSame('Foo!', (string) $response_a->getBody());

        // Check if fails with unsupported dependencies.
        $closure = fn (Router $router): string => 'I received a router!';
        $this->router->setRoute('router', $closure);
        $this->expectException(\RuntimeException::class);
        $this->router->route($this->getRequest('router'));
    }

    /**
     * @covers ::__construct
     * @covers ::findRoute
     * @covers ::route
     * @uses Laucov\WebFramework\Data\ArrayBuilder::setValue
     * @uses Laucov\WebFramework\Data\ArrayReader::__construct
     * @uses Laucov\WebFramework\Data\ArrayReader::getArray
     * @uses Laucov\WebFramework\Data\ArrayReader::validateKeys
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Http\AbstractIncomingMessage::__construct
     * @uses Laucov\WebFramework\Http\IncomingRequest::__construct
     * @uses Laucov\WebFramework\Http\Router::getClosureReturnTypes
     * @uses Laucov\WebFramework\Http\Router::getReflectionTypeNames
     * @uses Laucov\WebFramework\Http\Router::setRoute
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testThrowsACustomExceptionIfARouteIsNotFound(): void
    {
        $this->router->setRoute('foo/bar', fn (): string => 'You found me!');
        $this->expectException(NotFoundException::class);
        $this->router->route($this->getRequest('foo/baz'));
    }

    /**
     * @covers ::__construct
     * @covers ::setRoute
     * @covers ::popPrefix
     * @covers ::pushPrefix
     * @uses Laucov\WebFramework\Data\ArrayBuilder::setValue
     * @uses Laucov\WebFramework\Data\ArrayReader::__construct
     * @uses Laucov\WebFramework\Data\ArrayReader::getArray
     * @uses Laucov\WebFramework\Data\ArrayReader::validateKeys
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Files\StringSource::__toString
     * @uses Laucov\WebFramework\Http\AbstractIncomingMessage::__construct
     * @uses Laucov\WebFramework\Http\AbstractMessage::getBody
     * @uses Laucov\WebFramework\Http\AbstractOutgoingMessage::setBody
     * @uses Laucov\WebFramework\Http\IncomingRequest::__construct
     * @uses Laucov\WebFramework\Http\Route::getParameters
     * @uses Laucov\WebFramework\Http\Router::findRoute
     * @uses Laucov\WebFramework\Http\Router::getClosureReturnTypes
     * @uses Laucov\WebFramework\Http\Router::getReflectionTypeNames
     * @uses Laucov\WebFramework\Http\Router::route
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testTrimsAllPaths(): void
    {
        // Add routes.
        $this->router
            ->pushPrefix('foo/')
                ->setRoute('/bar', fn (): string => 'Bar!')
                ->setRoute('/baz/', fn (): string => 'Baz.')
            ->popPrefix()
            ->pushPrefix('/hello/')
                ->setRoute('world', fn (): string => 'Hello, World!')
                ->setRoute('people/', fn (): string => 'Hello, People!');
        
        // Test routes.
        $response_a = $this->router->route($this->getRequest('foo/bar'));
        $this->assertSame('Bar!', (string) $response_a->getBody());
        $response_b = $this->router->route($this->getRequest('foo/baz'));
        $this->assertSame('Baz.', (string) $response_b->getBody());
        $response_c = $this->router->route($this->getRequest('hello/world'));
        $this->assertSame('Hello, World!', (string) $response_c->getBody());
        $response_d = $this->router->route($this->getRequest('hello/people'));
        $this->assertSame('Hello, People!', (string) $response_d->getBody());
    }

    /**
     * Get a request with a custom URI path.
     */
    protected function getRequest(string $path): IncomingRequest
    {
        return new IncomingRequest(
            content_or_post: '',
            headers: [],
            protocol_version: '1.0',
            method: 'GET',
            uri: 'http://foobar.com/' . $path,
            parameters: [],
        );
    }
}
