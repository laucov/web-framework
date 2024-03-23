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

namespace Tests\Providers;

use Laucov\WebFwk\Providers\EnvMatch;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Providers\EnvMatch
 */
class EnvMatchTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testCanUseAsAttribute(): void
    {
        // Create instance.
        $env_name = new EnvMatch('SOME_ENV_VAR', 'myProperty');
        $this->assertSame('SOME_ENV_VAR', $env_name->variableName);
        $this->assertSame('myProperty', $env_name->propertyName);

        // Test as attribute.
        $object = new
        #[EnvMatch('MY_CLASS_NAME', 'name')]
        #[EnvMatch('MY_CLASS_COUNT', 'count')]
        class {
            public string $name = 'Foobar';
            public int $count = 18;
        };
    }
}
