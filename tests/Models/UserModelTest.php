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
use Laucov\Db\Data\Driver\DriverFactory;
use Laucov\WebFwk\Entities\User;
use Laucov\WebFwk\Models\UserModel;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Models\UserModel
 */
class UserModelTest extends TestCase
{
    protected UserModel $model;

    /**
     * @covers ::retrieveWithLogin
     * @uses Laucov\Modeling\Entity\AbstractEntity::__construct
     * @uses Laucov\Modeling\Entity\AbstractEntity::__set
     * @uses Laucov\Modeling\Model\AbstractModel::__construct
     * @uses Laucov\Modeling\Model\AbstractModel::applyDeletionFilter
     * @uses Laucov\Modeling\Model\AbstractModel::getEntities
     * @uses Laucov\Modeling\Model\AbstractModel::getEntity
     */
    public function testCanRetrieveWithLoginName(): void
    {
        // Test valid logins.
        $user = $this->model->retrieveWithLogin('john');
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame(1, $user->id);
        $user = $this->model->retrieveWithLogin('michael');
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame(2, $user->id);

        // Test invalid logins.
        $this->assertNull($this->model->retrieveWithLogin('leonard'));
    }

    protected function setUp(): void
    {
        // Create connection.
        $conn = new Connection(new DriverFactory(), 'sqlite::memory:');

        // Create tables and insert users.
        $pass_a = password_hash('1234', PASSWORD_DEFAULT);
        $pass_b = password_hash('4321', PASSWORD_DEFAULT);
        $conn
            ->query(<<<SQL
                CREATE TABLE "users" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                    "login" VARCHAR(64),
                    "password_hash" VARCHAR(256),
                    "deleted_at" DATETIME
                )
                SQL)
            ->query(<<<SQL
                INSERT INTO "users" ("login", "password_hash")
                VALUES
                    ('john', :pass_a),
                    ('michael', :pass_b)
                SQL, compact('pass_a', 'pass_b'));

        // Set model.
        $this->model = new UserModel($conn);
    }
}
