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

use Laucov\WebFwk\Validation\Rules\Length;
use Tests\AbstractRuleTest;

/**
 * @coversDefaultClass \Laucov\WebFwk\Validation\Rules\Length
 */
class LengthTest extends AbstractRuleTest
{
    public function dataProvider(): array
    {
        return [
            [[6], [0, 1, 2, 3, 6, 7, 8]],
            [[0, 6], [0, 4, 5, 6, 7, 8]],
            [[6, 6], [0, 6, 7, 8]],
            [[14], [1, 2, 3]],
            [[14, 14], [1, 2]],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::validate
     * @dataProvider dataProvider
     */
    public function testCanValidate(array $arguments, array $expected): void
    {
        $this->assertValidation(new Length(...$arguments), $expected);
    }

    /**
     * @covers ::validate
     * @uses Laucov\WebFwk\Validation\Rules\Length::__construct
     */
    public function testDoesNotAcceptNonScalarValues(): void
    {
        $this->assertRejectsNonScalarValues(new Length());
    }

    /**
     * @coversNothing
     */
    public function testIsPropertyAttribute(): void
    {
        $this->assertIsPropertyAttribute(Length::class);
    }

    protected function getValues(): array
    {
        return [
            0 => 'Foobar',
            1 => 'Lorem ipsum do',
            2 => '部分地区最高温升幅12℃以上',
            3 => 'The quick brown fox jumps over the lazy dog.',
            4 => true,
            5 => false,
            6 => 1112.2,
            7 => 111222,
            8 => 111222.0000,
        ];
    }
}
