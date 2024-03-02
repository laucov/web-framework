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

namespace Tests\Session;

use Laucov\WebFramework\Session\Handlers\FileSessionHandler;
use Laucov\WebFramework\Session\Handlers\Interfaces\SessionHandlerInterface;
use Laucov\WebFramework\Session\SessionOpening;
use Laucov\WebFramework\Session\SessionClosing;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Session\Handlers\FileSessionHandler
 */
class FileSessionHandlerTest extends TestCase
{
    /**
     * Handler.
     */
    protected FileSessionHandler $handler;

    /**
     * @covers ::__construct
     * @covers ::close
     * @covers ::create
     * @covers ::destroy
     * @covers ::open
     * @covers ::read
     * @covers ::regenerate
     * @covers ::write
     */
    public function testCanManipulateSessions(): void
    {
        // Check if implements the handler interface.
        $this->assertInstanceOf(
            SessionHandlerInterface::class,
            $this->handler,
        );

        // Get inexistent session.
        $this->assertSame(
            SessionOpening::NOT_FOUND,
            $this->handler->open('foobarbaz'),
        );

        // Create session.
        $id_a = $this->handler->create();
        $this->assertMatchesRegularExpression('/^[a-f\d]+$/', $id_a);

        // Get created session.
        $this->assertSame(
            SessionOpening::OPEN,
            $this->handler->open($id_a),
        );

        // Try to re-open session.
        $this->assertSame(
            SessionOpening::ALREADY_OPEN,
            $this->handler->open($id_a),
        );

        // Close the session.
        $this->assertSame(
            SessionClosing::CLOSED,
            $this->handler->close($id_a),
        );
        $this->assertSame(
            SessionClosing::NOT_FOUND,
            $this->handler->close($id_a),
        );

        // Re-open.
        $this->assertSame(
            SessionOpening::OPEN,
            $this->handler->open($id_a),
        );

        // Create concurring handler to test locks.
        $handler = new FileSessionHandler(__DIR__ . '/session-files');

        // Test lock properties and change them to not actually lock this test.
        $reflection = new \ReflectionObject($handler);
        $lock_sh = $reflection->getProperty('sharedLock');
        $this->assertSame(LOCK_SH, $lock_sh->getValue($handler));
        $lock_ex = $reflection->getProperty('exclusiveLock');
        $this->assertSame(LOCK_EX, $lock_ex->getValue($handler));
        $lock_sh->setValue($handler, LOCK_SH|LOCK_NB);
        $lock_ex->setValue($handler, LOCK_EX|LOCK_NB);

        // Test concurrency against default lock (LOCK_EX).
        $this->assertSame(
            SessionOpening::UNABLE_TO_LOCK,
            $handler->open($id_a),
        );
        $this->assertSame(
            SessionOpening::UNABLE_TO_LOCK,
            $handler->open($id_a, true),
        );

        // Test concurrency against readonly lock (LOCK_SH).
        $id_b = $this->handler->create();
        $this->assertSame(
            SessionOpening::OPEN,
            $this->handler->open($id_b, true),
        );
        $this->assertSame(
            SessionOpening::OPEN,
            $handler->open($id_b, true),
        );
        $handler->close($id_b);
        $this->assertSame(
            SessionOpening::UNABLE_TO_LOCK,
            $handler->open($id_b, false),
        );

        // Destroy a session.
        $this->handler->destroy($id_a);
        $this->assertSame(
            SessionOpening::NOT_FOUND,
            $this->handler->open($id_a),
        );

        // Write to the session.
        $this->assertSame('', $this->handler->read($id_b));
        $data = 'nome|s:4:"John";';
        $this->handler->write($id_b, $data);
        $this->assertSame($data, $this->handler->read($id_b));

        // Regenerate a session without destroying the old one.
        $id_c = $this->handler->regenerate($id_b, false);
        $this->assertNotSame($id_b, $id_c);

        // Check if is already open and contains the same data.
        $this->assertSame($data, $this->handler->read($id_c));
        
        // Check if the regenerated session is locked.
        $this->assertSame(
            SessionOpening::UNABLE_TO_LOCK,
            $handler->open($id_c, true),
        );

        // Check if the old session is closed and still contains the data.
        $this->assertSame(
            SessionOpening::OPEN,
            $this->handler->open($id_b),
        );
        $this->assertSame($data, $this->handler->read($id_b));

        // Regenerate destroying the old session.
        $id_d = $this->handler->regenerate($id_c, true);
        $this->assertNotSame($id_c, $id_d);
        $this->assertSame($data, $this->handler->read($id_d));
        $this->assertSame(
            SessionOpening::NOT_FOUND,
            $this->handler->open($id_c),
        );
    }
    
    protected function setUp(): void
    {
        $directory = __DIR__ . '/session-files';

        if (!is_dir($directory)) {
            mkdir($directory);
        }

        $this->handler = new FileSessionHandler($directory);
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
