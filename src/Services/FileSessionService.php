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

namespace Laucov\WebFwk\Services;

use Laucov\Sessions\Handlers\FileSessionHandler;
use Laucov\Sessions\Session;
use Laucov\WebFwk\Services\Interfaces\SessionServiceInterface;
use Laucov\WebFwk\Config\Session as SessionConfig;

/**
 * Provides read/write access to sessions stored in files.
 */
class FileSessionService implements SessionServiceInterface
{
    /**
     * Session handler.
     */
    protected FileSessionHandler $handler;

    /**
     * Create the service instance.
     */
    public function __construct(
        /**
         * Session configuration.
         */
        protected SessionConfig $config,
    ) {
        $this->handler = new FileSessionHandler($config->path);
    }

    /**
     * Create a new session.
     */
    public function createSession(): Session
    {
        return new Session($this->handler, $this->handler->create());
    }

    /**
     * Get a saved session.
     */
    public function getSession(string $id): Session
    {
        return new Session($this->handler, $id);
    }

    /**
     * Check if a session ID is valid.
     */
    public function validateId(string $id): bool
    {
        return $this->handler->validate($id);
    }
}
