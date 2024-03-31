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

namespace Tests\Entities;

use Laucov\WebFwk\Entities\User;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Entities\User
 */
class UserTest extends TestCase
{
    /**
     * @covers ::setPassword
     * @covers ::testPassword
     * @uses Laucov\Modeling\Entity\AbstractEntity::__construct
     */
    public function testSetsAndTestsPasswords(): void
    {
        // Create entity.
        $user = new User();
        $hash = password_hash('foobar', PASSWORD_DEFAULT);
        $user->password_hash = $hash;

        // Test passwords.
        $this->assertFalse($user->testPassword('1234'));
        $this->assertFalse($user->testPassword('foobarbaz'));
        $this->assertTrue($user->testPassword('foobar'));

        // Set new password.
        $user->setPassword('foobarbaz');
        $this->assertFalse($user->testPassword('1234'));
        $this->assertTrue($user->testPassword('foobarbaz'));
        $this->assertFalse($user->testPassword('foobar'));
    }
}
