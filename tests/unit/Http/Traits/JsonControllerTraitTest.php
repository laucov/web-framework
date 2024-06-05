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
use Laucov\Modeling\Entity\AbstractEntity;
use Laucov\Validation\Error;
use Laucov\Validation\Ruleset;
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
        $controller = new class () {
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
                return $path . (strlen($args) ? ":{$args}" : '');
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
                    '{"messages":[{"content":"error.invalid_json","type":"err'
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
        $controller = new class () {
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

    /**
     * @covers ::getEntity
     * @uses Laucov\WebFwk\Http\Traits\JsonControllerTrait::setJson
     */
    public function testCanValidateAndGetEntitiesFromData(): void
    {
        // Create ruleset.
        $this->createMock(Ruleset::class);

        // Create example entity.
        $entity = new class () extends AbstractEntity {
            public static array $validationErrors;
            public string $name;
            public int $age;
            public function validate(): bool
            {
                $this->errors = static::$validationErrors;
                return count(static::$validationErrors) === 0;
            }
        };

        // Mock dependencies.
        $response = $this->createMock(OutgoingResponse::class);

        // Create controller.
        $controller = new class () {
            use JsonControllerTrait;
            public null|AbstractEntity $entity;
            public null|HttpException $exception;
            public OutgoingResponse $response;
            public function test(array $data, string $class_name): void
            {
                $this->entity = null;
                $this->exception = null;
                try {
                    $this->entity = $this->getEntity($data, $class_name);
                } catch (HttpException $e) {
                    $this->exception = $e;
                }
            }
            protected function findMessage($path, $args = []): string
            {
                $format = fn ($k, $v) => sprintf('%s=%s', $k, $v);
                $args = array_map($format, array_keys($args), $args);
                $args = implode(';', $args);
                return $path . (strlen($args) ? ":{$args}" : '');
            }
        };

        // Set properties.
        $controller->response = $response;

        // Set expectations.
        $response
            ->expects($this->exactly(3))
            ->method('setBody')
            ->withConsecutive(
                [
                    '{"messages":[{"content":"error.invalid_entity_fields:coun'
                        . 't=2;list=\"name\" (string);last=\"age\" (int)","typ'
                        . 'e":"error"}]}',
                ],
                [
                    '{"errors":{"age":["validation.\\\\Path\\\\To\\\\Rule"]}}',
                ],
                [
                    '{"errors":{"age":["Custom message..."]}}',
                ],
            )
            ->willReturnSelf();
        $response
            ->expects($this->exactly(3))
            ->method('setHeaderLine')
            ->withConsecutive(
                ['Content-Type', 'application/json'],
                ['Content-Type', 'application/json'],
                ['Content-Type', 'application/json'],
            )
            ->willReturnSelf();
        $response
            ->expects($this->exactly(3))
            ->method('setStatus')
            ->withConsecutive(
                [422, 'Unprocessable Entity'],
                [422, 'Unprocessable Entity'],
                [422, 'Unprocessable Entity'],
            );

        // Test with invalid property types.
        $data = ['name' => ['invalid_type'], 'age' => ['invalid_type']];
        $controller->test($data, $entity::class);
        $this->assertNull($controller->entity);
        $this->assertNotNull($controller->exception);

        // Test with validation errors.
        $error = $this->getMockBuilder(Error::class)
            ->setConstructorArgs(['\Path\To\Rule', [], null])
            ->getMock();
        $entity::class::$validationErrors = ['age' => [$error]];
        $controller->test([], $entity::class);
        $this->assertNull($controller->entity);
        $this->assertNotNull($controller->exception);

        // Test with validation errors (custom error messages).
        $error->message = 'Custom message...';
        $controller->test([], $entity::class);
        $this->assertNull($controller->entity);
        $this->assertNotNull($controller->exception);

        // Test with valid data.
        $entity::class::$validationErrors = [];
        $controller->test(['name' => 'John', 'age' => 40], $entity::class);
        $this->assertNotNull($controller->entity);
        $this->assertNull($controller->exception);
        $this->assertSame('John', $controller->entity->{'name'});
        $this->assertSame(40, $controller->entity->{'age'});
    }
}
