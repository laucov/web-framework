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

use Laucov\WebFramework\Validation\Rules\Interfaces\RuleInterface;
use PHPUnit\Framework\TestCase;

/**
 * Provides a method to get an object's rule attribute.
 */
abstract class AbstractRuleTest extends TestCase
{
    /**
     * Rule class name.
     */
    protected string $className;

    /**
     * Get rules to test.
     * 
     * This function must return an array in the following format:
     * 
     * `[RuleInterface, array<int>][]`
     * 
     * Where:
     * 
     * - `RuleInterface` is the rule instance to filter `getValues()` values;
     * - `array<int>` is a list of expected indexes for the filtered array.
     */
    public abstract function ruleProvider(): array;

    /**
     * Get values to validate.
     * 
     * This function must return key-value pairs for filtering.
     */
    protected abstract function getValues(): array;

    /**
     * Assert that a class can be used as a property attribute.
     */
    protected function assertIsPropertyAttribute(string $class_name): void
    {
        // Get class attributes.
        $reflection = new \ReflectionClass($class_name);
        $attributes = $reflection->getAttributes();
        
        // Check if the class is a property attribute.
        foreach ($attributes as $attribute) {
            // Check if is an attribute.
            if ($attribute->getName() !== \Attribute::class) {
                continue;
            }
            // Check if targets properties.
            $argument = $attribute->getArguments()[0] ?? null;
            if ($argument !== \Attribute::TARGET_PROPERTY) {
                continue;
            }
            $this->assertTrue(true);
            return;
        }

        // Fail if couldn't find an `Attribute` attribute.
        $message = 'Failed to assert that %s is a property attribute.';
        $this->fail(sprintf($message, $class_name));
    }

    /**
     * Assert that a rule invalidates non-scalar values.
     */
    protected function assertRejectsNonScalarValues(RuleInterface $rule): void
    {
        // Set example values.
        $values = [
            null,
            [],
            [[]],
            [1, 2],
            ['a', 'b', 'c'],
            [null, 'a', 1.23, []],
            new \stdClass(),
            fopen('data://text/plain,foobar', 'r'),
            function () {},
            fn ($foo) => 'bar',
        ];

        // Test each value.
        foreach ($values as $value) {
            if ($rule->validate($value)) {
                // Set detailed error message.
                $message = 'Failed to assert that %s only accepts scalar '
                    . 'values. The following value passed validation: %s';
                $export = var_export($value, true);
                $class_name = get_class($rule);
                $this->fail(sprintf($message, $class_name, $export));
            }
        }

        $this->assertTrue(true);
    }

    /**
     * Test all values from `ruleProvider()` with `$rule->validate()`.
     * 
     * Assert that the valid values correspond to the indexes in `$expected`.
     * 
     * @param RuleInterface $rule Rule to test validation
     * @param array $expected Valid indexes from `ruleProvider()`'s values
     */
    protected function assertValidation(RuleInterface $rule, array $expected): void
    {
        $actual = array_filter($this->getValues(), [$rule, 'validate']);
        $actual = '[' . implode(', ', array_keys($actual)) . ']';
        $expected = '[' . implode(', ', $expected) . ']';
        $this->assertSame($expected, $actual);
    }
}
