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
use Laucov\WebFwk\Entities\UserAuthnMethod;
use Laucov\WebFwk\Models\UserAuthnMethodModel;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Models\UserAuthnMethodModel
 */
class UserMfaMethodModelTest extends TestCase
{
    protected UserAuthnMethodModel $model;

    public function authnMethodIdProvider(): array
    {
        return [
            ['14', '1', true],
            ['23', '2', true],
            ['14', '3', true],
            ['23', '1', false],
            ['89', '1', false],
        ];
    }

    /**
     * @covers ::listForUser
     * @uses Laucov\Modeling\Entity\AbstractEntity::__construct
     * @uses Laucov\Modeling\Entity\AbstractEntity::__set
     * @uses Laucov\Modeling\Model\AbstractModel::__construct
     * @uses Laucov\Modeling\Model\AbstractModel::applyDeletionFilter
     * @uses Laucov\Modeling\Model\AbstractModel::getEntities
     * @uses Laucov\Modeling\Model\AbstractModel::list
     * @uses Laucov\Modeling\Model\AbstractModel::resetPagination
     * @uses Laucov\Modeling\Model\Collection::__construct
     * @uses Laucov\Modeling\Model\Collection::count
     * @uses Laucov\Modeling\Model\Collection::get
     */
    public function testCanListForUsers(): void
    {
        $collection = $this->model->listForUser('14');
        $this->assertCount(2, $collection);
        $this->assertSame(1, $collection->get(0)->id);
        $this->assertSame(3, $collection->get(1)->id);
        $collection = $this->model->listForUser('23');
        $this->assertCount(1, $collection);
        $this->assertSame(2, $collection->get(0)->id);
    }

    /**
     * @covers ::existsForUser
     * @covers ::retrieveForUser
     * @uses Laucov\Modeling\Entity\AbstractEntity::__construct
     * @uses Laucov\Modeling\Entity\AbstractEntity::__set
     * @uses Laucov\Modeling\Model\AbstractModel::__construct
     * @uses Laucov\Modeling\Model\AbstractModel::applyDeletionFilter
     * @uses Laucov\Modeling\Model\AbstractModel::getEntities
     * @uses Laucov\Modeling\Model\AbstractModel::getEntity
     * @uses Laucov\Modeling\Model\AbstractModel::retrieve
     * @dataProvider authnMethodIdProvider
     */
    public function testCanRetrieveForUsers(
        string $user_id,
        string $id,
        bool $should_exist,
    ): void {
        // Check if exists.
        $exists = $this->model->existsForUser($user_id, ...[$id]);
        $this->assertSame($should_exist, $exists);

        // Try to get the record.
        $record = $this->model->retrieveForUser($user_id, $id);

        // Check result.
        if ($should_exist) {
            $this->assertInstanceOf(UserAuthnMethod::class, $record);
            $this->assertSame((int) $id, $record->id);
        } else {
            $this->assertNull($record);
        }
    }

    protected function setUp(): void
    {
        // Create connection.
        $conn = new Connection(new DriverFactory(), 'sqlite::memory:');

        // Create tables and insert users.
        $conn
            ->query(<<<SQL
                CREATE TABLE "users_authn_methods" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                    "user_id" INT(11),
                    "name" VARCHAR(64),
                    "data" VARCHAR(512),
                    "deleted_at" DATETIME
                )
                SQL)
            ->query(<<<SQL
                INSERT INTO "users_authn_methods" ("user_id", "name", "data")
                VALUES
                    (14, 'sms', '{"number":"+5511999999999"}'),
                    (23, 'totp', '{"secret":"123"}'),
                    (14, 'ssl', '{"public_key": "abc"}')
                SQL);

        // Set model.
        $this->model = new UserAuthnMethodModel($conn);
    }
}
