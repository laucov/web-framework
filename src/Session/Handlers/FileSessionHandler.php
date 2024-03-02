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

namespace Laucov\WebFramework\Session\Handlers;

use Laucov\WebFramework\Session\Handlers\Interfaces\SessionHandlerInterface;
use Laucov\WebFramework\Session\SessionClosing;
use Laucov\WebFramework\Session\SessionOpening;

/**
 * Allows reading and writing sessions using files.
 */
class FileSessionHandler implements SessionHandlerInterface
{
    /**
     * Base directory.
     */
    protected string $directory;

    /**
     * Exclusive lock value.
     */
    protected int $exclusiveLock = LOCK_EX;

    /**
     * Open sessions.
     * 
     * @var array<string, resource>
     */
    protected array $sessions = [];

    /**
     * Shared lock value.
     */
    protected int $sharedLock = LOCK_SH;

    /**
     * Create the session handler instance.
     */
    public function __construct(string $directory)
    {
        $this->directory = rtrim($directory, '\\/') . DIRECTORY_SEPARATOR;
    }

    /**
     * Close the session with the given ID.
     */
    public function close(string $id): SessionClosing
    {
        if (!array_key_exists($id, $this->sessions)) {
            return SessionClosing::NOT_FOUND;
        }

        fclose($this->sessions[$id]);
        unset($this->sessions[$id]);

        return SessionClosing::CLOSED;
    }

    /**
     * Create a new session.
     */
    public function create(): string
    {
        $id = uniqid();
        touch($this->directory . $id);
        
        return $id;
    }

    /**
     * Destroy the session with the given ID.
     */
    public function destroy(string $id): void
    {
        $this->open($id);
        $this->close($id);
        unlink($this->directory . $id);
        return;
    }

    /**
     * Open the session with the given ID.
     */
    public function open(string $id, bool $readonly = false): SessionOpening
    {
        // Check if session is already open.
        if (array_key_exists($id, $this->sessions)) {
            return SessionOpening::ALREADY_OPEN;
        }

        // Check if session exists.
        if (!file_exists($this->directory . $id)) {
            return SessionOpening::NOT_FOUND;
        }

        // Open session.
        $resource = fopen($this->directory . $id, 'a+');

        // Lock session file.
        $lock = $readonly ? $this->sharedLock : $this->exclusiveLock;
        if (!flock($resource, $lock)) {
            return SessionOpening::UNABLE_TO_LOCK;
        }

        // Register open session.
        $this->sessions[$id] = $resource;

        return SessionOpening::OPEN;
    }

    /**
     * Read all data from the session with the given ID.
     */
    public function read(string $id): string
    {
        $resource = $this->sessions[$id];
        return stream_get_contents($resource, null, 0) ?: '';
    }

    /**
     * Regenerate a session.
     */
    public function regenerate(string $id, bool $delete_old_session): string
    {
        // Get old session data.
        $data = $this->read($id);

        // Create ID.
        $new_id = uniqid();

        // Create file and copy the old session content.
        $resource = fopen($this->directory . $new_id, 'c+');
        if (
            !$resource
            || !flock($resource, $this->exclusiveLock)
            || !ftruncate($resource, 0)
            || !fwrite($resource, $data)
        ) {
            $msg = 'Could not lock, truncate or write the new session file.';
            throw new \RuntimeException($msg);
        }

        // Register new session.
        $this->sessions[$new_id] = $resource;

        // Close old session.
        fclose($this->sessions[$id]);
        unset($this->sessions[$id]);

        // Delete old session.
        if ($delete_old_session) {
            unlink($this->directory . $id);
        }

        return $new_id;
    }

    /**
     * Write data to the session with the given ID.
     */
    public function write(string $id, string $data): void
    {
        $resource = $this->sessions[$id];
        ftruncate($resource, 0);
        rewind($resource);
        fwrite($resource, $data);
    }
}
