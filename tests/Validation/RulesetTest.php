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

namespace Tests\Validation;

use Laucov\WebFramework\Validation\Rules\Length;
use Laucov\WebFramework\Validation\Rules\Regex;
use Laucov\WebFramework\Validation\Ruleset;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Validation\Ruleset
 */
class RulesetTest extends TestCase
{
    /**
     * @covers ::addRule
     * @covers ::getErrors
     * @covers ::validate
     * @uses Laucov\WebFramework\Validation\Rules\Length::__construct
     * @uses Laucov\WebFramework\Validation\Rules\Length::validate
     * @uses Laucov\WebFramework\Validation\Rules\Regex::__construct
     * @uses Laucov\WebFramework\Validation\Rules\Regex::validate
     */
    public function testCanAddRulesAndValidate(): void
    {
        // Create ruleset instance.
        $ruleset = new Ruleset();

        // Add rules.
        $ruleset->addRule(new Length(4), new Regex('/^foo/'));

        // Violate rule #1.
        $this->assertFalse($ruleset->validate('foo'));
        $errors = $ruleset->getErrors();
        $this->assertIsArray($errors);
        $this->assertCount(1, $errors);
        $this->assertInstanceOf(Length::class, $errors[0]);

        // Violate rule #2.
        $this->assertFalse($ruleset->validate('barfoo'));
        $errors = $ruleset->getErrors();
        $this->assertIsArray($errors);
        $this->assertCount(1, $errors);
        $this->assertInstanceOf(Regex::class, $errors[0]);

        // Violate both rules.
        $this->assertFalse($ruleset->validate('bar'));
        $errors = $ruleset->getErrors();
        $this->assertIsArray($errors);
        $this->assertCount(2, $errors);
        $this->assertInstanceOf(Length::class, $errors[0]);
        $this->assertInstanceOf(Regex::class, $errors[1]);

        // Test valid value.
        $this->assertTrue($ruleset->validate('foobar'));
    }
}
