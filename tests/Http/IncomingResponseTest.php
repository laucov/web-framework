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

use Laucov\WebFramework\Http\IncomingResponse;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Http\IncomingResponse
 */
class IncomingResponseTest extends TestCase
{
    /**
     * @covers ::__construct
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Files\StringSource::read
     * @uses Laucov\WebFramework\Http\AbstractIncomingMessage::__construct
     * @uses Laucov\WebFramework\Http\AbstractMessage::getBody
     * @uses Laucov\WebFramework\Http\AbstractMessage::getHeader
     * @uses Laucov\WebFramework\Http\Traits\ResponseTrait::getStatusCode
     * @uses Laucov\WebFramework\Http\Traits\ResponseTrait::getStatusText
     */
    public function testCanInstantiate(): void
    {
        $response = new IncomingResponse(
            content: 'Some message.',
            headers: [
                'Authorization' => 'Basic user:password',
            ],
            status_code: 401,
            status_text: 'Unauthorized',
        );
        $this->assertSame('Some message.', $response->getBody()->read(13));
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Unauthorized', $response->getStatusText());
        $header = $response->getHeader('Authorization');
        $this->assertSame('Basic user:password', $header);
    }
}
