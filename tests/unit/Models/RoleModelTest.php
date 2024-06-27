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

namespace Tests\Unit\Http\Traits;

use Laucov\Db\Data\Connection;
use Laucov\WebFwk\Models\RoleModel;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\ModelTestCase;
use Tests\QueryExpectation;

/**
 * @coversDefaultClass \Laucov\WebFwk\Models\RoleModel
 */
class RoleModelTest extends ModelTestCase
{
    /**
     * Connection mock.
     */
    public Connection&MockObject $conn;

    /**
     * Model instance.
     */
    public RoleModel $model;

    /**
     * Query expectations.
     *
     * @var array<QueryExpectation>
     */
    protected array $queryExpectations = [];

    /**
     * @covers ::forUser
     */
    public function testCanGetUserRoles(): void
    {
        // Expect queries.
        $this->expectQuery()
            ->withTemplate(<<<SQL
                SELECT COUNT(roles.id) AS id
                FROM roles
                LEFT JOIN users_roles
                ON roles.id = users_roles.role_id
                WHERE users_roles.user_id = {users_roles_user_id}
                AND users_roles.deleted_at IS NULL
                AND roles.deleted_at IS NULL
                GROUP BY roles.id
                SQL)
            ->withParameter('users_roles_user_id', 10);
        $this->expectQuery()
            ->withTemplate(<<<SQL
                SELECT roles.id
                name
                FROM roles
                LEFT JOIN users_roles
                ON roles.id = users_roles.role_id
                WHERE users_roles.user_id = {users_roles_user_id}
                AND users_roles.deleted_at IS NULL
                AND roles.deleted_at IS NULL
                GROUP BY roles.id
                SQL)
            ->withParameter('users_roles_user_id', 10);
        $this->expectQuery()
            ->withTemplate(<<<SQL
                SELECT COUNT(id) AS id
                FROM roles
                SQL);

        // Run methods.
        $model = new RoleModel($this->mockConnection());
        $model
            ->forUser(10)
            ->listAll();
    }
}
