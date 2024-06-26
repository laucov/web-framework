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

namespace Tests\Models;

use Laucov\Db\Data\Connection;
use Laucov\WebFwk\Entities\User;
use Laucov\WebFwk\Models\UserModel;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\ModelTestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Models\UserModel
 * @todo UserModel::withRoles
 * @todo RoleModel::withEntityPermissions
 * @todo RoleModel::withEntityPropertyPermissions
 * @todo RoleModel::withCustomPermissions
 * @todo PermissionGuard
 * @todo ServerGuard
 */
class UserModelTest extends ModelTestCase
{
    /**
     * Connection mock.
     */
    protected MockObject & Connection $conn;

    /**
     * Model instance.
     */
    protected UserModel $model;

    /**
     * @covers ::withPerson
     */
    public function testCanJoinPerson(): void
    {
        // Retrieve with person.
        $this->expectQuery()
            ->withTemplate(<<<SQL
                SELECT users.id,
                login,
                password_hash,
                authentication_steps
                FROM users
                LEFT JOIN persons
                ON users.person_id = persons.id
                AND persons.deleted_at IS NULL
                WHERE users.id = {users_id}
                AND users.deleted_at IS NULL
                SQL)
            ->withParameter('users_id', '1');
        $conn = $this->mockConnection();
        $conn
            ->method('listClass')
            ->willReturnOnConsecutiveCalls(
                [$this->createMock(User::class)],
                [$this->createMock(User::class)],
            );
        $model = new UserModel($conn);
        $model
            ->withPerson()
            ->retrieve('1');

        // List with person.
        $this->expectQuery()
            ->withTemplate(<<<SQL
                SELECT COUNT(users.id) AS id
                FROM users
                LEFT JOIN persons
                ON users.person_id = persons.id
                AND persons.deleted_at IS NULL
                WHERE users.deleted_at IS NULL
                SQL);
        $this->expectQuery()
            ->withTemplate(<<<SQL
                SELECT users.id,
                login,
                password_hash,
                authentication_steps
                FROM users
                LEFT JOIN persons
                ON users.person_id = persons.id
                AND persons.deleted_at IS NULL
                WHERE users.deleted_at IS NULL
                SQL);
        $this->expectQuery()
            ->withTemplate(<<<SQL
                SELECT COUNT(id) AS id
                FROM users
                SQL);
        $model = new UserModel($this->mockConnection());
        $model
            ->withPerson()
            ->listAll();
    }

    /**
     * @covers ::retrieveWithLogin
     */
    public function testCanRetrieveWithLoginName(): void
    {
        // Create query template.
        $template = <<<SQL
            SELECT users.id,
            login,
            password_hash,
            authentication_steps
            FROM users
            WHERE login = {login}
            AND users.deleted_at IS NULL
            SQL;

        // Set expectations.
        $this->expectQuery()
            ->withTemplate($template)
            ->withParameter('login', 'john');
        $this->expectQuery()
            ->withTemplate($template)
            ->withParameter('login', 'michael');
        $this->expectQuery()
            ->withTemplate($template)
            ->withParameter('login', 'leonard');

        // Mock connection.
        $conn = $this->mockConnection();
        $conn
            ->method('listClass')
            ->willReturnOnConsecutiveCalls(
                [$this->createMock(User::class)],
                [$this->createMock(User::class)],
                [],
            );

        // Create model.
        $model = new UserModel($conn);

        // Simulate valid logins.
        $this->assertNotNull($model->retrieveWithLogin('john'));
        $this->assertNotNull($model->retrieveWithLogin('michael'));

        // Simulate invalid login.
        $this->assertNull($model->retrieveWithLogin('leonard'));
    }

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        // Mock connection.
        $this->conn = $this->createMock(Connection::class);

        // Create model.
        $this->model = new UserModel($this->conn);
    }
}
