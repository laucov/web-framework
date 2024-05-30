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

namespace Tests\Unit\Http\Traits;

use Laucov\Files\Resource\StringSource;
use Laucov\Http\Message\OutgoingResponse;
use Laucov\Http\Message\RequestInterface;
use Laucov\Http\Routing\Exceptions\HttpException;
use Laucov\WebFwk\Entities\Input;
use Laucov\WebFwk\Http\Traits\JsonControllerTrait;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Http\Traits\JsonControllerTrait
 */
class JsonControllerTraitTest extends TestCase
{
    /**
     * @covers ::getJson
     * @uses Laucov\WebFwk\Http\Traits\JsonControllerTrait::setJson
     */
    public function testCanGetAndValidateRequestJsonData(): void
    {
        // Mock dependencies.
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(OutgoingResponse::class);

        // Create controller.
        $controller = new class {
            use JsonControllerTrait;
            public null|HttpException $exception = null;
            public null|Input $input = null;
            public OutgoingResponse $response;
            public function test(RequestInterface $request): void
            {
                $this->exception = null;
                $this->input = null;
                try {
                    $this->input = $this->getJson($request);
                } catch (HttpException $e) {
                    $this->exception = $e;
                }
            }
            protected function findMessage($path, $args = []): string
            {
                $args = implode(',', $args);
                return "{$path}:{$args}";
            }
        };

        // Set properties.
        $controller->response = $response;

        // Configure mocks.
        $mock_body = function (string $content) {
            return $this->getMockBuilder(StringSource::class)
                ->setMethodsExcept(['__toString'])
                ->setConstructorArgs([$content])
                ->getMock();
        };
        $request
            ->method('getBody')
            ->willReturn(
                $mock_body('{not_a_valid_json}'),
                $mock_body('{"data":"I am not an array."}'),
                $mock_body('{"foo":"bar","baz":"???"}'),
                $mock_body('{"data":{"foo":"bar","baz":["Hello","World"]}}'),
            );
        $request
            ->method('getHeaderLine')
            ->willReturn(
                'text/plain',
                'application/json',
                'application/json',
                'application/json',
                'application/json',
            );
        $response
            ->expects($this->exactly(3))
            ->method('setStatus')
            ->withConsecutive(
                [415, 'Unsupported Media Type'],
                [400, 'Bad Request'],
                [422, 'Unprocessable Entity'],
            );
        $response
            ->expects($this->exactly(3))
            ->method('setHeaderLine')
            ->willReturnSelf();
        $response
            ->expects($this->exactly(3))
            ->method('setBody')
            ->withConsecutive(
                [
                    '{"messages":[{"content":"error.unsupported_mime_type:text'
                        . '\/plain","type":"error"},{"content":"info.supported'
                        . '_mime_types:application\/json","type":"info"}]}',
                ],
                [
                    '{"messages":[{"content":"error.invalid_json:","type":"err'
                        . 'or"}]}',
                ],
                [
                    '{"messages":[{"content":"error.invalid_input_field:\"data'
                        . '\" (array)","type":"error"}]}',
                ],
            );

        // Test.
        $controller->test($request);
        $this->assertNull($controller->input);
        $controller->test($request);
        $this->assertNull($controller->input);
        $controller->test($request);
        $this->assertNull($controller->input);
        $controller->test($request);
        $this->assertNotNull($controller->input);
        $controller->test($request);
        $this->assertNotNull($controller->input);
    }

    /**
     * @covers ::setJson
     */
    public function testCanSetResponseJsonData(): void
    {
        // Mock dependencies.
        $response = $this->createMock(OutgoingResponse::class);

        // Create controller.
        $controller = new class {
            use JsonControllerTrait;
            public OutgoingResponse $response;
            public function test(): void
            {
                $this->setJson(["text" => "Hello, World!"]);
            }
        };

        // Set properties.
        $controller->response = $response;

        // Set expectations.
        $response
            ->expects($this->once())
            ->method('setBody')
            ->with('{"text":"Hello, World!"}');
        $response
            ->expects($this->once())
            ->method('setHeaderLine')
            ->with('Content-Type', 'application/json')
            ->willReturnSelf();

        // Run.
        $controller->test();
    }
}
