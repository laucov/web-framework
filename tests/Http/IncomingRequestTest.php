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

use Laucov\WebFramework\Http\IncomingRequest;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Http\IncomingRequest
 */
class IncomingRequestTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getPostVariables
     * @uses Laucov\WebFramework\Data\ArrayReader::__construct
     * @uses Laucov\WebFramework\Data\ArrayReader::getValue
     * @uses Laucov\WebFramework\Data\ArrayReader::validateKeys
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Files\StringSource::read
     * @uses Laucov\WebFramework\Http\AbstractIncomingMessage::__construct
     * @uses Laucov\WebFramework\Http\AbstractMessage::getBody
     * @uses Laucov\WebFramework\Http\IncomingRequest::__construct
     */
    public function testCanInstantiateWithPostVariablesArray(): void
    {
        $request = $this->getInstance([
            'name' => 'John',
            'age' => '32',
            'fruits' => ['apple', 'tomato'],
        ]);

        $this->assertSame('', $request->getBody()->read(10));
        
        $fruit = $request->getPostVariables()->getValue(['fruits', 1]);
        $this->assertSame('tomato', $fruit);
    }

    /**
     * @covers ::__construct
     * @uses Laucov\WebFramework\Data\ArrayReader::__construct
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Files\StringSource::read
     * @uses Laucov\WebFramework\Http\AbstractIncomingMessage::__construct
     * @uses Laucov\WebFramework\Http\AbstractMessage::getBody
     * @uses Laucov\WebFramework\Http\IncomingRequest::__construct
     */
    public function testCanInstantiateWithTextAndFile(): void
    {
        $text_request = $this->getInstance('Text content.');
        $text = $text_request->getBody()->read(13);
        $this->assertSame('Text content.', $text);

        $file = fopen('data://text/plain,File content.', 'r');
        $file_request = $this->getInstance($file);
        $text = $file_request->getBody()->read(13);
        $this->assertSame('File content.', $text);
    }

    /**
     * @covers ::__construct
     * @covers ::getParameters
     * @uses Laucov\WebFramework\Data\ArrayReader::__construct
     * @uses Laucov\WebFramework\Data\ArrayReader::getValue
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Http\AbstractIncomingMessage::__construct
     * @uses Laucov\WebFramework\Http\IncomingRequest::__construct
     */
    public function testCanGetParameters(): void
    {
        $request = $this->getInstance('Any content.');
        $parameter = $request->getParameters()->getValue('search');
        $this->assertSame('foobar', $parameter);
    }

    /**
     * Get a request pre-configured instance.
     */
    public function getInstance(mixed $content): IncomingRequest
    {
        // Create headers.
        $headers = [
            'Authorization' => 'Basic john.doe:1234',
        ];

        // Create parameters.
        $parameters = [
            'page' => '2',
            'search' => 'foobar',
        ];

        // Create request.
        return new IncomingRequest(
            content_or_post: $content,
            headers: $headers,
            parameters: $parameters,
        );
    }
}
