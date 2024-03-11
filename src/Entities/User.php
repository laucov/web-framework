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

namespace Laucov\WebFramework\Entities;

use Laucov\WebFramework\Modeling\AbstractEntity;

/**
 * Represents a user record.
 */
class User extends AbstractEntity
{
    /**
     * User ID.
     */
    public int $id;

    /**
     * Login.
     */
    public string $login;

    /**
     * Password hash.
     */
    public string $password_hash;

    /**
     * Number of required successful MFA procedures to log in.
     */
    public int $required_mfa;

    /**
     * Set a new password hash from the given password.
     */
    public function setPassword(string $password): void
    {
        $this->password_hash = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Check if a password matches the registered hash.
     */
    public function testPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }
}
