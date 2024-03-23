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

namespace Tests\Services;

use Laucov\WebFwk\Config\Session as SessionConfig;
use Laucov\WebFwk\Services\FileSessionService;
use Laucov\WebFwk\Services\Interfaces\SessionServiceInterface;
use Laucov\Sessions\Session;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Services\FileSessionService
 */
class FileSessionServiceTest extends TestCase
{
    /**
     * Configuration object.
     */
    protected SessionConfig $config;

    /**
     * Service object.
     */
    protected FileSessionService $service;

    /**
     * @covers ::__construct
     * @covers ::createSession
     * @covers ::getSession
     * @uses Laucov\Sessions\Handlers\FileSessionHandler::__construct
     * @uses Laucov\Sessions\Handlers\FileSessionHandler::close
     * @uses Laucov\Sessions\Handlers\FileSessionHandler::create
     * @uses Laucov\Sessions\Handlers\FileSessionHandler::open
     * @uses Laucov\Sessions\Handlers\FileSessionHandler::read
     * @uses Laucov\Sessions\Handlers\FileSessionHandler::write
     * @uses Laucov\Sessions\Session::__construct
     * @uses Laucov\Sessions\Session::close
     * @uses Laucov\Sessions\Session::commit
     * @uses Laucov\Sessions\Session::get
     * @uses Laucov\Sessions\Session::open
     * @uses Laucov\Sessions\Session::set
     */
    public function testCanGetSession(): void
    {
        // Create service.
        $this->assertInstanceOf(SessionServiceInterface::class, $this->service);

        // Get session.
        $session_a = $this->service->createSession();
        $this->assertInstanceOf(Session::class, $session_a);
        $id = $session_a->id;
        $session_b = $this->service->getSession($id);
        $this->assertInstanceOf(Session::class, $session_b);
        $this->assertNotSame($session_a, $session_b);

        // Check if both objects refer to the same session.
        $this->assertSame($session_a->id, $session_b->id);
        $session_a
            ->open()
            ->set('foo', 'bar')
            ->commit();
        $this->assertSame('bar', $session_b->open()->get('foo'));
    }

    protected function setUp(): void
    {
        $this->config = new SessionConfig();
        $this->config->path = __DIR__ . '/session-files';
        $this->service = new FileSessionService($this->config);
    }

    protected function tearDown(): void
    {
        $items = array_diff(scandir(__DIR__ . '/session-files'), ['.', '..']);
        foreach ($items as $item) {
            $filename = __DIR__ . "/session-files/{$item}";
            if (!is_dir($filename)) {
                unlink($filename);
            }
        }
    }
}
