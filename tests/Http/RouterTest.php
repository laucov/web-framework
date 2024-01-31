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
use Laucov\WebFramework\Http\OutgoingRequest;
use Laucov\WebFramework\Http\OutgoingResponse;
use Laucov\WebFramework\Http\RequestInterface;
use Laucov\WebFramework\Http\ResponseInterface;
use Laucov\WebFramework\Http\Route;
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
     * @covers ::findRoute
     * @covers ::popPrefix
     * @covers ::pushPrefix
     * @covers ::setPattern
     * @covers ::setRoute
     * @uses Laucov\Arrays\ArrayBuilder::setValue
     * @uses Laucov\Arrays\ArrayReader::__construct
     * @uses Laucov\Arrays\ArrayReader::getValue
     * @uses Laucov\Arrays\ArrayReader::validateKeys
     * @uses Laucov\Files\Resource\StringSource::__construct
     * @uses Laucov\Files\Resource\StringSource::__toString
     * @uses Laucov\WebFramework\Http\AbstractIncomingMessage::__construct
     * @uses Laucov\WebFramework\Http\AbstractMessage::getBody
     * @uses Laucov\WebFramework\Http\AbstractOutgoingMessage::setBody
     * @uses Laucov\WebFramework\Http\IncomingRequest::__construct
     * @uses Laucov\WebFramework\Http\Route::__construct
     * @uses Laucov\WebFramework\Http\Route::run
     * @uses Laucov\WebFramework\Http\RouteClosure::__construct
     * @uses Laucov\WebFramework\Http\RouteClosure::findClosureParameterTypes
     * @uses Laucov\WebFramework\Http\RouteClosure::findClosureReturnType
     * @uses Laucov\WebFramework\Http\Router::__construct
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getMethod
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\Files\Resource\Uri::__construct
     * @uses Laucov\Files\Resource\Uri::fromString
     */
    public function testCanSetAndFindRoutes(): void
    {
        // Set route.
        $closure_a = fn(): string => 'Output A';
        $this->assertSame(
            $this->router,
            $this->router->setRoute('GET', 'path/to/route-a', $closure_a),
        );

        // Get existent route.
        $route_a = $this->findRoute('GET', 'path/to/route-a');
        $this->assertInstanceOf(Route::class, $route_a);
        $this->assertSame('Output A', (string) $route_a->run()->getBody());

        // Get inexistent route with inexistent segment.
        $this->assertNull($this->findRoute('GET', 'path/to/route-b'));
        // Get inexistent route with intermediary segment.
        $this->assertNull($this->findRoute('GET', 'path/to'));
        // Get inexistent route with wrong method.
        $this->assertNull($this->findRoute('POST', 'path/to/route-a'));

        // Test router's path trimming.
        $closure_b = fn(): string => 'Output B';
        $this->router->setRoute('POST', 'path/to/route-b/', $closure_b);
        $route_b = $this->findRoute('POST', 'path/to/route-b');
        $this->assertInstanceOf(Route::class, $route_b);
        $this->assertSame('Output B', (string) $route_b->run()->getBody());
        $closure_c = fn(): string => 'Output C';
        $this->router->setRoute('PUT', '/path/to/route-c', $closure_c);
        $route_c = $this->findRoute('PUT', 'path/to/route-c');
        $this->assertInstanceOf(Route::class, $route_c);
        $this->assertSame('Output C', (string) $route_c->run()->getBody());
        $closure_d = fn(): string => 'Output D';
        $this->router->setRoute('PATCH', '/path/to/route-d/', $closure_d);
        $route_d = $this->findRoute('PATCH', 'path/to/route-d');
        $this->assertInstanceOf(Route::class, $route_d);
        $this->assertSame('Output D', (string) $route_d->run()->getBody());

        // Set patterns.
        $this->assertSame(
            $this->router,
            $this->router->setPattern('int', '/^[0-9]+$/'),
        );
        $this->router->setPattern('alpha', '/^[A-Za-z]+$/');

        // Test without parameters.
        $closure_e = fn(): string => 'Output E';
        $this->router->setRoute('POST', 'routes/:alpha', $closure_e);
        $route_e = $this->findRoute('POST', 'routes/e');
        $this->assertInstanceOf(Route::class, $route_e);
        $this->assertSame('Output E', (string) $route_e->run()->getBody());

        // Test with parameters.
        $closure_f = fn(string $a): string => sprintf('Output %s', $a);
        $this->router->setRoute('GET', 'routes/:int', $closure_f);
        $route_f = $this->findRoute('GET', 'routes/123');
        $this->assertInstanceOf(Route::class, $route_f);
        $this->assertSame('Output 123', (string) $route_f->run()->getBody());

        // Test with request argument.
        $closure_g = function (
            string $a,
            RequestInterface $b,
            string $c,
        ): string {
            $host = $b->getUri()->host;
            return "{$a}, {$host}, {$c}";
        };
        $this->router->setRoute('POST', 'routes/:int/test/:alpha', $closure_g);
        $route_g = $this->findRoute('POST', 'routes/123/test/abc');
        $this->assertInstanceOf(Route::class, $route_g);
        $content_g = (string) $route_g->run()->getBody();
        $this->assertSame('123, foobar.com, abc', $content_g);

        // Test with variadic string argument.
        $closure_h = function (string $a, string ...$b): string {
            return $a . ', ' . implode(', ', $b);
        };
        $path_h = 'foos/:int/bars/:int/bazes/:int';
        $this->router->setRoute('POST', $path_h, $closure_h);
        $route_h = $this->findRoute('POST', 'foos/1/bars/9/bazes/0');
        $this->assertInstanceOf(Route::class, $route_h);
        $content_h = (string) $route_h->run()->getBody();
        $this->assertSame('1, 9, 0', $content_h);

        // Test pushing prefix.
        $this->assertSame($this->router, $this->router->pushPrefix('prefix'));
        $this->router->setRoute('GET', 'path/a', fn (): string => 'Path A');
        $route_i = $this->findRoute('GET', 'prefix/path/a');
        $this->assertInstanceOf(Route::class, $route_i);
        $content_i = (string) $route_i->run()->getBody();
        $this->assertSame('Path A', $content_i);

        // Test popping prefix.
        $this->assertSame($this->router, $this->router->popPrefix());
        $this->router->setRoute('GET', 'path/b', fn (): string => 'Path B');
        $route_j = $this->findRoute('GET', 'path/b');
        $this->assertInstanceOf(Route::class, $route_j);
        $content_j = (string) $route_j->run()->getBody();
        $this->assertSame('Path B', $content_j);

        // Test prefix trimming.
        $this->router
            ->pushPrefix('/animals')
                ->setRoute('GET', 'dog', fn (): string => 'Dog!')
            ->popPrefix()
            ->pushPrefix('plants/')
                ->setRoute('GET', 'tree', fn (): string => 'Tree!')
            ->popPrefix()
            ->pushPrefix('/plants/')
                ->pushPrefix('flowers')
                    ->setRoute('GET', 'poppy', fn (): string => 'Poppy!')
                ->popPrefix()
            ->popPrefix();
        $tests = [
            ['animals/dog', 'Dog!'],
            ['plants/tree', 'Tree!'],
            ['plants/flowers/poppy', 'Poppy!'],
        ];
        foreach ($tests as $test) {
            $route = $this->findRoute('GET', $test[0]);
            $this->assertInstanceOf(Route::class, $route);
            $content = (string) $route->run()->getBody();
            $this->assertSame($test[1], $content);
        }
    }

    /**
     * Find a route using a generic request with the given method and path.
     */
    protected function findRoute(string $method, string $path): null|Route
    {
        $request = new IncomingRequest(
            content_or_post: 'Hello, World!',
            headers: [],
            protocol_version: null,
            method: $method,
            uri: 'http://foobar.com/' . $path,
            parameters: [],
        );

        return $this->router->findRoute($request);
    }
}
