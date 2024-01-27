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

use Laucov\WebFramework\Http\OutgoingRequest;
use Laucov\WebFramework\Http\OutgoingResponse;
use Laucov\WebFramework\Http\ResponseInterface;
use Laucov\WebFramework\Http\Router;
use Laucov\WebFramework\Web\Uri;
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
     * @covers ::addRoute
     * @covers ::route
     * @uses Laucov\WebFramework\Data\ArrayReader::__construct
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Files\StringSource::getSize
     * @uses Laucov\WebFramework\Files\StringSource::read
     * @uses Laucov\WebFramework\Http\AbstractMessage::getBody
     * @uses Laucov\WebFramework\Http\AbstractOutgoingMessage::setBody
     * @uses Laucov\WebFramework\Http\OutgoingRequest::__construct
     * @uses Laucov\WebFramework\Http\OutgoingRequest::setUri
     * @uses Laucov\WebFramework\Http\Traits\RequestTrait::getUri
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
     */
    public function testCanRoute(): void
    {
        $this->assertInstanceOf(
            Router::class,
            $this->router->addRoute('hello-world', function () {
                $response = new OutgoingResponse();
                $response->setBody('Hello, World!');
                return $response;
            }),
        );

        $request = new OutgoingRequest();
        $request->setUri(Uri::fromString('http://foobar.com/hello-world'));
        $response = $this->router->route($request);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $length = $response->getBody()->getSize();
        $content = $response->getBody()->read($length);
        $this->assertSame('Hello, World!', $content);
    }
}
