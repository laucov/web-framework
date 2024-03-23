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

namespace Tests\Validation\Rules;

use Laucov\WebFwk\Validation\Rules\Contains;
use Tests\AbstractRuleTest;

/**
 * @coversDefaultClass \Laucov\WebFwk\Validation\Rules\Contains
 */
class ContainsTest extends AbstractRuleTest
{
    public function dataProvider(): array
    {
        return [
            [['quick'], [0]],
            [[' '], [0, 1]],
            [['.'], [0, 2]],
            [['@', '.'], [0, 2, 3]],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::validate
     * @dataProvider dataProvider
     */
    public function testCanValidate(array $arguments, array $expected): void
    {
        $this->assertValidation(new Contains(...$arguments), $expected);
    }

    /**
     * @covers ::validate
     * @uses Laucov\WebFwk\Validation\Rules\Contains::__construct
     */
    public function testDoesNotAcceptNonScalarValues(): void
    {
        $this->assertRejectsNonScalarValues(new Contains());
    }

    /**
     * @coversNothing
     */
    public function testIsPropertyAttribute(): void
    {
        $this->assertIsPropertyAttribute(Contains::class, true);
    }

    protected function getValues(): array
    {
        return [
            0 => 'The quick brown fox jumps over the lazy dog.',
            1 => 'Lorem ipsum dolor sit amet',
            2 => 'john.doe@foobar.com',
            3 => 'johndoe@localhost',
        ];
    }
}
