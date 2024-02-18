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

namespace Tests\Modeling;

use Laucov\WebFramework\Modeling\Entity;
use Laucov\WebFramework\Validation\Rules\Length;
use Laucov\WebFramework\Validation\Rules\Regex;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Modeling\Entity
 */
class EntityTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::cacheRules
     * @covers ::getErrors
     * @covers ::getRuleset
     * @covers ::toArray
     * @covers ::validate
     * @uses Laucov\WebFramework\Validation\Rules\Length::__construct
     * @uses Laucov\WebFramework\Validation\Rules\Length::validate
     * @uses Laucov\WebFramework\Validation\Rules\Regex::__construct
     * @uses Laucov\WebFramework\Validation\Rules\Regex::validate
     * @uses Laucov\WebFramework\Validation\Ruleset::addRule
     * @uses Laucov\WebFramework\Validation\Ruleset::getErrors
     * @uses Laucov\WebFramework\Validation\Ruleset::validate
     */
    public function testCanValidate(): void
    {
        // Create entity instance.
        $entity = new class extends Entity
        {
            #[Length(8, 16)]
            public string $login;
            #[Length(16, 24)]
            #[Regex('/[A-Z]+/')]
            #[Regex('/[a-z]+/')]
            #[Regex('/\d+/')]
            #[Regex('/[\!\#\$\%\&\@]+/')]
            public string $password;
        };

        // Validate valid values.
        $entity->login = 'john.doe';
        $entity->password = 'Secret_Pass#1234';
        $this->assertTrue($entity->validate());

        // Set invalid value.
        $entity->login = 'john.manoel.foobar.doe';
        $this->assertFalse($entity->validate());
        $errors = $entity->getErrors('login');
        $this->assertIsArray($errors);
        $this->assertCount(1, $errors);
        $this->assertInstanceOf(Length::class, $errors[0]);
        $errors = $entity->getErrors('password');
        $this->assertCount(0, $errors);

        // Fix and add another invalid value.
        $entity->login = 'john.foobar';
        $entity->password = 'ABCDEF';
        $this->assertFalse($entity->validate());
        $errors = $entity->getErrors('login');
        $this->assertCount(0, $errors);
        $errors = $entity->getErrors('password');
        $this->assertIsArray($errors);
        $this->assertCount(4, $errors);
        $this->assertInstanceOf(Length::class, $errors[0]);
        $this->assertInstanceOf(Regex::class, $errors[1]);
        $this->assertInstanceOf(Regex::class, $errors[2]);
        $this->assertInstanceOf(Regex::class, $errors[3]);

        // Fix again.
        $entity->password = 'SECUREpass@987654321';
        $this->assertTrue($entity->validate());

        // Get as array.
        $array = $entity->toArray();
        $this->assertIsArray($array);
        $this->assertSame($array['login'], 'john.foobar');
        $this->assertSame($array['password'], 'SECUREpass@987654321');
    } 
}
