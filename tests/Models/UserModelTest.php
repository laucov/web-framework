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
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\RegularExpression;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Models\UserModel
 * @todo UserModel::withRoles
 * @todo RoleModel::withEntityPermissions
 * @todo RoleModel::withEntityPropertyPermissions
 * @todo RoleModel::withCustomPermissions
 * @todo PermissionGuard
 * @todo ServerGuard
 */
class UserModelTest extends TestCase
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
        // Mock methods.
        $this->conn
            ->method('quoteIdentifier')
            ->willReturnCallback(fn ($value) => $value);
        $this->conn
            ->method('fetchAssoc')
            ->willReturn(['id' => 999999]);
        $this->conn
            ->method('listClass')
            ->willReturnOnConsecutiveCalls(
                [$this->createMock(User::class)],
                [$this->createMock(User::class)],
            );
        
        // Set expectations.
        $this->conn
            ->expects($this->exactly(4))
            ->method('query')
            ->withConsecutive(
                [
                    $this->matchesQueryWithPlaceholders(<<<SQL
                        SELECT users.id,
                        login,
                        password_hash,
                        authentication_steps
                        FROM users
                        LEFT JOIN persons
                        ON users.person_id = persons.id
                        AND persons.deleted_at IS NULL
                        WHERE users.id = :users_id_UNIQID
                        AND users.deleted_at IS NULL
                        SQL),
                    $this->arrayContains([
                        'key' => $this->stringStartsWith('users_id_'),
                        'offset' => 0,
                        'value' => $this->identicalTo('1'),
                    ]),
                ],
                [
                    $this->matchesQueryWithPlaceholders(<<<SQL
                        SELECT COUNT(users.id) AS id
                        FROM users
                        LEFT JOIN persons
                        ON users.person_id = persons.id
                        AND persons.deleted_at IS NULL
                        WHERE users.deleted_at IS NULL
                        SQL),
                    [],
                ],
                [
                    $this->matchesQueryWithPlaceholders(<<<SQL
                        SELECT users.id,
                        login,
                        password_hash,
                        authentication_steps
                        FROM users
                        LEFT JOIN persons
                        ON users.person_id = persons.id
                        AND persons.deleted_at IS NULL
                        WHERE users.deleted_at IS NULL
                        SQL),
                    [],
                ],
            );
        
        // Retrieve with person.
        $this->model
            ->withPerson()
            ->retrieve('1');

        // List with person.
        $this->model
            ->withPerson()
            ->listAll();
    }

    /**
     * @covers ::retrieveWithLogin
     */
    public function testCanRetrieveWithLoginName(): void
    {
        // Mock methods.
        $this->conn
            ->method('quoteIdentifier')
            ->willReturnCallback(fn ($value) => $value);
        $this->conn
            ->method('listClass')
            ->willReturnOnConsecutiveCalls(
                [$this->createMock(User::class)],
                [$this->createMock(User::class)],
                [],
            );

        // Create query template.
        $query_template = <<<SQL
            SELECT users.id,
            login,
            password_hash,
            authentication_steps
            FROM users
            WHERE login = :login_UNIQID
            AND users.deleted_at IS NULL
            SQL;
        
        // Set expectations.
        $this->conn
            ->expects($this->exactly(3))
            ->method('query')
            ->withConsecutive(
                [
                    $this->matchesQueryWithPlaceholders($query_template),
                    $this->arrayContains([
                        'key' => $this->stringStartsWith('login_'),
                        'offset' => 0,
                        'value' => $this->identicalTo('john'),
                    ]),
                ],
                [
                    $this->matchesQueryWithPlaceholders($query_template),
                    $this->arrayContains([
                        'key' => $this->stringStartsWith('login_'),
                        'offset' => 0,
                        'value' => $this->identicalTo('michael'),
                    ]),
                ],
                [
                    $this->matchesQueryWithPlaceholders($query_template),
                    $this->arrayContains([
                        'key' => $this->stringStartsWith('login_'),
                        'offset' => 0,
                        'value' => $this->identicalTo('leonard'),
                    ]),
                ],
            );

        // Simulate valid logins.
        $this->assertNotNull($this->model->retrieveWithLogin('john'));
        $this->assertNotNull($this->model->retrieveWithLogin('michael'));
        // Simulate invalid logins.
        $this->assertNull($this->model->retrieveWithLogin('leonard'));
    }

    /**
     * Create a complex array search.
     * 
     * Each `$searches` item must contain an array with three elements:
     * 
     * - Key constraint, such as `$this->stringStartsWith('foo_')`;
     * - Key offset (gets the nth key that matches the constraint);
     * - Value constraint, such as `$this->exactly('baz')`;
     */
    protected function arrayContains(array ...$searches): Callback
    {
        // Create callback.
        $callback = function ($array) use ($searches) {
            // Check if is array.
            if (!is_array($array)) {
                return false;
            }
            // Check each search.
            foreach ($searches as $constraints) {
                $key_constraint = $constraints[0] ?? $constraints['key'];
                $offset = $constraints[1] ?? $constraints['offset'];
                $value_constraint = $constraints[2] ?? $constraints['value'];
                // Format constraints.
                $key_constraint = $key_constraint instanceof Constraint
                    ? $key_constraint
                    : $this->exactly($key_constraint);
                $value_constraint = $value_constraint instanceof Constraint
                    ? $value_constraint
                    : $this->exactly($value_constraint);
                // Initialize index.
                $index = 0;
                // Check each key.
                foreach ($array as $key => $value) {
                    // Check if the key matches.
                    if ($key_constraint->evaluate($key, '', true)) {
                        // Wait for the correct count.
                        if ($index < $offset) {
                            $index++;
                            continue;
                        }
                        // Test value.
                        if (!$value_constraint->evaluate($value, '', true)) {
                            return false;
                        } else {
                            continue 2;
                        }
                    }
                }
                // Not enough keys matched.
                return false;
            }
            return true;
        };

        return $this->callback($callback);
    }

    /**
     * Create a query match constraint compatible with random parameter keys.
     * 
     * Use "UNIQID" as a placeholder for the `uniqid` pattern.
     * 
     * Example:
     * 
     * For queries like `SELECT * FROM foo WHERE bar = :param_6675cd86872dc`
     * use `SELECT * FROM foo WHERE bar = :param_UNIQID`.
     */
    protected function matchesQueryWithPlaceholders(
        string $query_template,
    ): RegularExpression {
        $patt = preg_quote($query_template);
        $patt = str_replace('UNIQID', '[a-z0-9]+', $patt);
        return $this->matchesRegularExpression('/' . $patt . '/');
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
