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

namespace Tests;

use Laucov\Db\Data\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\QueryExpectation;

/**
 * Provides methods for testing models.
 * 
 * @template T of AbstractModel
 */
class ModelTestCase extends TestCase
{
    /**
     * Query expectations.
     * 
     * @var array<QueryExpectation>
     */
    protected array $queryExpectations = [];

    /**
     * Expect the connection to receive a query call.
     */
    protected function expectQuery(): QueryExpectation
    {
        $expectation = new QueryExpectation();
        $this->queryExpectations[] = $expectation;
        return $expectation;
    }

    /**
     * Mock the connection class.
     */
    protected function mockConnection(): MockObject&Connection
    {
        // Create mock.
        $connection = $this->createMock(Connection::class);

        // Mock methods.
        $connection
            ->method('quoteIdentifier')
            ->willReturnCallback(fn ($value) => $value);
        $connection
            ->method('fetchAssoc')
            ->willReturn(['id' => 999]);

        // Setup query expectations.
        $arguments = [];
        foreach ($this->queryExpectations as $expectation) {
            $arguments[] = [
                $expectation->getTextConstraint(),
                $expectation->getParamsConstraint(),
            ];
        }
        $count = count($this->queryExpectations);
        $connection
            ->expects($this->exactly($count))
            ->method('query')
            ->withConsecutive(...$arguments);
        
        return $connection;
    }
}
