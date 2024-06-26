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

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsIdentical;

/**
 * Represents a connection query expectation.
 */
class QueryExpectation extends Assert
{
    /**
     * Expected params.
     */
    public array $params;

    /**
     * Expected text.
     */
    public string $template;

    /**
     * Assert a query text.
     */
    public function assertText(string $template, string $text): void
    {
        $pattern = '/\{([A-Za-z0-9_]+)\}/';
        $template = preg_replace($pattern, ':$1_UNIQID', $template);
        $template = preg_quote($template);
        $template = str_replace('UNIQID', '[a-z0-9]+', $template);
        $this->assertIsString($text);
        $this->assertMatchesRegularExpression("/{$template}/", $text);
    }

    /**
     * Validate query parameters.
     */
    public function assertParameters(array $expected, mixed $params): void
    {
        // Check if is array.
        $this->assertIsArray($params);

        // Make sure all expectations are constraint objects.
        $expected = array_map(function ($constraints) {
            return array_map(function ($constraint) {
                return $constraint instanceof Constraint
                    ? $constraint
                    : new IsIdentical($constraint);
            }, $constraints);
        }, $expected);

        // Format parameters.
        $actual = [];
        foreach ($params as $key => $value) {
            $prefix = implode('_', array_slice(explode('_', $key), 0, -1));
            $actual[$prefix][] = $value;
        }

        // Validate parameters with the constraints.
        foreach ($expected as $name => $constraints) {
            $values = $actual[$name] ?? [];
            foreach ($constraints as $offset => $constraint) {
                $message = 'Assert that the #%s "%s" exists.';
                $message = sprintf($message, $offset, $name);
                $this->assertArrayHasKey($offset, $values, $message);
                $this->assertThat($values[$offset], $constraint);
            }
        }
    }

    /**
     * Parse the expectation as a callback test constraint.
     */
    public function getParamsConstraint(): Callback
    {
        return new Callback(function ($actual) {
            if (isset($this->params)) {
                $this->assertParameters($this->params, $actual);
            }
            return true;
        });
    }

    /**
     * Parse the expectation as a regular expression test constraint.
     */
    public function getTextConstraint(): Callback
    {
        return new Callback(function ($actual) {
            if (isset($this->template)) {
                $this->assertText($this->template, $actual);
            }
            return true;
        });
    }

    /**
     * Set the expected query text.
     */
    public function withParameter(string $name, mixed $constraint): static
    {
        $this->params[$name][] = $constraint;
        return $this;
    }

    /**
     * Set the expected query text.
     */
    public function withTemplate(string $template): static
    {
        $this->template = $template;
        return $this;
    }
}
