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
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getMethod
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testCanPrefix(): void
    {
        // Test pushing prefixes.
        $this->assertSame($this->router, $this->router->pushPrefix('prefix'));
        $this->router->setRoute('POST', 'some-route', function (): string {
            return 'Some output.';
        });
        $this->router
            ->pushPrefix('foobar')
            ->setRoute('PATCH', 'the-route', fn (): string => 'The output.');
        
        // Test popping prefixes.
        $this->assertSame($this->router, $this->router->popPrefix());
        $this->router->setRoute('GET', 'another-route', function (): string {
            return 'Another output.';
        });
        $this->router
            ->popPrefix()
            ->setRoute('GET', 'final-route', fn (): string => 'Final output.');

        // Test routes.
        $request_a = $this->getRequest('POST', 'prefix/some-route');
        $response_a = $this->router->route($request_a);
        $this->assertSame('Some output.', (string) $response_a->getBody());
        $request_b = $this->getRequest('PATCH', 'prefix/foobar/the-route');
        $response_b = $this->router->route($request_b);
        $this->assertSame('The output.', (string) $response_b->getBody());
        $request_c = $this->getRequest('GET', 'prefix/another-route');
        $response_c = $this->router->route($request_c);
        $this->assertSame('Another output.', (string) $response_c->getBody());
        $request_d = $this->getRequest('GET', 'final-route');
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
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getMethod
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
        $this->router->setRoute('PUT', 'foobars/:int', $closure);
        $request_a = $this->getRequest('PUT', 'foobars/8');
        $response_a = $this->router->route($request_a);
        $this->assertSame('Foobar #8', (string) $response_a->getBody());

        // Test pattern with variadic parameters.
        $closure = function (string $a, string ...$b): string {
            return $a . ', ' . implode(', ', $b);
        };
        $path = 'foobars/:int/:int/bazes/:int';
        $this->router->setRoute('GET', $path, $closure);
        $request_b = $this->getRequest('GET', 'foobars/123/4/bazes/5');
        $response_b = $this->router->route($request_b);
        $this->assertSame('123, 4, 5', (string) $response_b->getBody());
    }

    /**
     * @covers ::route
     * @uses Laucov\WebFramework\Data\ArrayBuilder::setValue
     * @uses Laucov\WebFramework\Data\ArrayReader::__construct
     * @uses Laucov\WebFramework\Data\ArrayReader::getArray
     * @uses Laucov\WebFramework\Data\ArrayReader::validateKeys
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Http\AbstractIncomingMessage::__construct
     * @uses Laucov\WebFramework\Http\IncomingRequest::__construct
     * @uses Laucov\WebFramework\Http\Route::addParameter
     * @uses Laucov\WebFramework\Http\Route::getParameters
     * @uses Laucov\WebFramework\Http\Router::__construct
     * @uses Laucov\WebFramework\Http\Router::findRoute
     * @uses Laucov\WebFramework\Http\Router::getClosureReturnTypes
     * @uses Laucov\WebFramework\Http\Router::getReflectionTypeNames
     * @uses Laucov\WebFramework\Http\Router::setPattern
     * @uses Laucov\WebFramework\Http\Router::setRoute
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getMethod
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testClosureArgumentCountMustBeCompatibleWithPath(): void
    {
        // Add pattern.
        $result = $this->router->setPattern('int', '/^[0-9]+$/');
        $this->assertSame($this->router, $result);

        // Test with invalid argument count.
        $closure = fn (string $a, string $b, string $c): string => 'Hello!';
        $this->router->setRoute('PATCH', 'foo/:int/:int', $closure);
        $this->expectException(\RuntimeException::class);
        $this->router->route($this->getRequest('PATCH', 'foo/12/34'));
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
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getMethod
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testClosureMustReturnResponseOrStringOrStringable(): void
    {
        // Create response route.
        $closure_a = function (): ResponseInterface {
            $response = new OutgoingResponse();
            return $response->setBody('This is a response.');
        };
        $this->router->setRoute('PATCH', 'route-a', $closure_a);

        // Create string route.
        $this->router->setRoute('POST', 'route-b', function (): string {
            return 'This is a response.';
        });

        // Create Stringable route.
        $this->router->setRoute('PUT', 'route-c', function (): \Stringable {
            return new class () implements \Stringable {
                public function __toString(): string
                {
                    return 'This is a response.';
                }
            };
        });

        // Check results.
        $expected = 'This is a response.';
        $request_a = $this->getRequest('PATCH', 'route-a');
        $response_a = $this->router->route($request_a);
        $this->assertSame($expected, (string) $response_a->getBody());
        $request_b = $this->getRequest('POST', 'route-b');
        $response_b = $this->router->route($request_b);
        $this->assertSame($expected, (string) $response_b->getBody());
        $request_c = $this->getRequest('PUT', 'route-c');
        $response_c = $this->router->route($request_c);
        $this->assertSame($expected, (string) $response_c->getBody());

        // Test closure with union type.
        $closure_d = function (): string|\Stringable {
            return 'Testing with union types!';
        };
        $this->router->setRoute('GET', 'route-d', $closure_d);

        // Test invalid closure.
        $this->expectException(\InvalidArgumentException::class);
        $this->router->setRoute('POST', 'route-e', function (): string|array {
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
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getMethod
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testDifferentiatesIntermediaryAndFinalSegments(): void
    {
        $closure = fn (): string => 'You found me!';
        $this->router->setRoute('GET', 'foo/bar', $closure);
        $this->expectException(NotFoundException::class);
        $this->router->route($this->getRequest('GET', 'foo'));
    }

    /**
     * @covers ::route
     * @uses Laucov\WebFramework\Data\ArrayBuilder::setValue
     * @uses Laucov\WebFramework\Data\ArrayReader::__construct
     * @uses Laucov\WebFramework\Data\ArrayReader::getArray
     * @uses Laucov\WebFramework\Data\ArrayReader::validateKeys
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Http\AbstractIncomingMessage::__construct
     * @uses Laucov\WebFramework\Http\IncomingRequest::__construct
     * @uses Laucov\WebFramework\Http\Route::getParameters
     * @uses Laucov\WebFramework\Http\Router::__construct
     * @uses Laucov\WebFramework\Http\Router::findRoute
     * @uses Laucov\WebFramework\Http\Router::getClosureReturnTypes
     * @uses Laucov\WebFramework\Http\Router::getReflectionTypeNames
     * @uses Laucov\WebFramework\Http\Router::setRoute
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getMethod
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testDoesNotSupportClosuresWithUnionOrIntersectTypes(): void
    {
        $closure = fn (string|RequestInterface $a): string => 'Some output';
        $this->router->setRoute('POST', 'foo', $closure);
        $this->expectException(\RuntimeException::class);
        $this->router->route($this->getRequest('POST', 'foo'));
    }

    /**
     * @covers ::findRoute
     * @covers ::route
     * @uses Laucov\WebFramework\Data\ArrayBuilder::setValue
     * @uses Laucov\WebFramework\Data\ArrayReader::__construct
     * @uses Laucov\WebFramework\Data\ArrayReader::getArray
     * @uses Laucov\WebFramework\Data\ArrayReader::validateKeys
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Http\AbstractIncomingMessage::__construct
     * @uses Laucov\WebFramework\Http\IncomingRequest::__construct
     * @uses Laucov\WebFramework\Http\Router::__construct
     * @uses Laucov\WebFramework\Http\Router::getClosureReturnTypes
     * @uses Laucov\WebFramework\Http\Router::getReflectionTypeNames
     * @uses Laucov\WebFramework\Http\Router::setRoute
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getMethod
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testUsesMethodToRoute(): void
    {
        $closure = fn (string|RequestInterface $a): string => 'My output';
        $this->router->setRoute('POST', 'bar', $closure);
        $this->expectException(NotFoundException::class);
        $this->router->route($this->getRequest('GET', 'bar'));
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
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getMethod
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testPassesDependencies(): void
    {
        // Add routes.
        $closure = fn (RequestInterface $request): string => 'Foo!';
        $this->router->setRoute('PATCH', 'foo', $closure);

        // Test routes.
        $response_a = $this->router->route($this->getRequest('PATCH', 'foo'));
        $this->assertSame('Foo!', (string) $response_a->getBody());

        // Check if fails with unsupported dependencies.
        $closure = fn (Router $router): string => 'I received a router!';
        $this->router->setRoute('GET', 'router', $closure);
        $this->expectException(\RuntimeException::class);
        $this->router->route($this->getRequest('GET', 'router'));
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
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getMethod
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testThrowsACustomExceptionIfARouteIsNotFound(): void
    {
        $closure = fn (): string => 'You found me!';
        $this->router->setRoute('POST', 'foo/bar', $closure);
        $this->expectException(NotFoundException::class);
        $this->router->route($this->getRequest('POST', 'foo/baz'));
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
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getMethod
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testTrimsAllPaths(): void
    {
        // Add routes.
        $this->router
            ->pushPrefix('foo/')
                ->setRoute('POST', '/bar', fn (): string => 'Bar!')
                ->setRoute('PATCH', '/baz/', fn (): string => 'Baz.')
            ->popPrefix()
            ->pushPrefix('/hello/')
                ->setRoute('PUT', 'world', fn (): string => 'Hello, World!')
                ->setRoute('GET', 'people/', fn (): string => 'Hi, People!');
        
        // Test routes.
        $request_a = $this->getRequest('POST', 'foo/bar');
        $response_a = $this->router->route($request_a);
        $this->assertSame('Bar!', (string) $response_a->getBody());
        $request_b = $this->getRequest('PATCH', 'foo/baz');
        $response_b = $this->router->route($request_b);
        $this->assertSame('Baz.', (string) $response_b->getBody());
        $request_c = $this->getRequest('PUT', 'hello/world');
        $response_c = $this->router->route($request_c);
        $this->assertSame('Hello, World!', (string) $response_c->getBody());
        $request_d = $this->getRequest('GET', 'hello/people');
        $response_d = $this->router->route($request_d);
        $this->assertSame('Hi, People!', (string) $response_d->getBody());
    }

    /**
     * Get a request with a custom URI path.
     */
    protected function getRequest(
        string $method,
        string $path,
    ): IncomingRequest {
        return new IncomingRequest(
            content_or_post: '',
            headers: [],
            protocol_version: '1.0',
            method: $method,
            uri: 'http://foobar.com/' . $path,
            parameters: [],
        );
    }
}
