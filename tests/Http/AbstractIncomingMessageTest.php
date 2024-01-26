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

use Laucov\WebFramework\Http\AbstractIncomingMessage;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Http\AbstractIncomingMessage
 */
class AbstractIncomingMessageTest extends TestCase
{
    /**
     * @covers ::__construct
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Files\StringSource::read
     * @uses Laucov\WebFramework\Http\AbstractMessage::getBody
     * @uses Laucov\WebFramework\Http\AbstractMessage::getHeader
     * @uses Laucov\WebFramework\Http\AbstractMessage::getHeaderAsList
     */
    public function testCanInstantiate(): void
    {
        // Create instance.
        $message = $this->getInstance([
            'content' => 'The quick brown fox jumps over the lazy dog.',
            'headers' => [
                'Cache-Control' => 'must-understand, no-store',
                'Content-Length' => '44',
            ],
        ]);

        // Check body.
        /** @var \Laucov\WebFramework\Files\StringSource */
        $body = $message->getBody();
        $this->assertNotNull($body);
        $this->assertSame('The quick', $body->read(9));

        // Check headers.
        $this->assertSame('44', $message->getHeader('Content-Length'));
        $list = $message->getHeaderAsList('Cache-Control');
        $this->assertCount(2, $list);
        $this->assertContains('must-understand', $list);
        $this->assertContains('no-store', $list);
    }

    /**
     * @covers ::__construct
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     */
    public function testMustPassStringHeaders(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->getInstance([
            'content' => '',
            'headers' => [
                'Cache-Control' => ['must-understand', 'no-store'],
                'Content-Length' => 44,
            ],
        ]);
    }

    /**
     * Get a mock for `AbstractIncomingMessage`.
     */
    protected function getInstance(array $arguments): AbstractIncomingMessage
    {
        $class_name = AbstractIncomingMessage::class;
        return $this->getMockForAbstractClass($class_name, $arguments);
    }
}
