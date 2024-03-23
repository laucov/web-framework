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

namespace Laucov\WebFwk\Validation;

use Laucov\WebFwk\Validation\Rules\Interfaces\RuleInterface;

/**
 * Stores rules and validate values with them.
 */
class Ruleset
{
    /**
     * Current errors.
     * 
     * @var array<string>
     */
    protected array $errors = [];

    /**
     * Registered rules.
     * 
     * @var array<RuleInterface>
     */
    protected array $rules = [];

    /**
     * Add a new rule.
     */
    public function addRule(RuleInterface ...$rules): static
    {
        array_push($this->rules, ...$rules);
        return $this;
    }

    /**
     * Get current errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Validate a value with all registered rules.
     */
    public function validate(mixed $value): bool
    {
        $this->errors = [];

        foreach ($this->rules as $rule) {
            if (!$rule->validate($value)) {
                $this->errors[] = $rule;
            }
        }

        return count($this->errors) === 0;
    }
}
