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

namespace Laucov\WebFramework\Modeling;

use Laucov\WebFramework\Validation\Rules\Interfaces\RuleInterface;
use Laucov\WebFramework\Validation\Ruleset;

/**
 * Represents a database record.
 */
abstract class Entity
{
    /**
     * Errors found.
     * 
     * @var array<string, RuleInterface[]>
     */
    protected array $errors = [];

    /**
     * Cached property rules.
     * 
     * @var array<string, Ruleset>
     */
    protected array $rules = [];

    /**
     * Whether rules are already cached.
     */
    protected bool $rulesAreCached = false;

    /**
     * Anonymous function to get the entity's public values.
     */
    protected \Closure $valuesGetter;

    /**
     * Create the entity instance.
     */
    public function __construct()
    {
        // Set the getter function.
        // Necessary to get only the entity's public properties.
        $values_getter = fn (Entity $e): array => get_object_vars($e);
        $this->valuesGetter = $values_getter->bindTo($this, null);
    }

    /**
     * Get current errors.
     */
    public function getErrors(string $name): array
    {
        return $this->errors[$name] ?? [];
    }

    /**
     * Get the entity's data as an array.
     * 
     * @var array<string, mixed>
     */
    public function toArray(): array
    {
        return ($this->valuesGetter)($this);
    }

    /**
     * Validate current values.
     */
    public function validate(): bool
    {
        // Reset errors and get stored values.
        $this->errors = [];
        $values = $this->toArray();

        // Validate each value.
        foreach ($values as $name => $value) {
            $ruleset = $this->getRuleset($name);
            if (!$ruleset->validate($value)) {
                $this->errors[$name] = $ruleset->getErrors();
            }
        }

        return count($this->errors) === 0;
    }

    /**
     * Cache the entity's rules.
     */
    protected function cacheRules(): void
    {
        // Get properties.
        $class = new \ReflectionClass(static::class);
        /** @var \ReflectionProperty[] */
        $props = $class->getProperties(\ReflectionProperty::IS_PUBLIC);

        // Extract each properties' rules.
        foreach ($props as $prop) {
            // Get name and instantiate the ruleset.
            $name = $prop->getName();
            $this->rules[$name] = new Ruleset();
            // Get attributes and add each rule.
            /** @var \ReflectionAttribute[] */
            $attributes = $prop->getAttributes(
                RuleInterface::class,
                \ReflectionAttribute::IS_INSTANCEOF,
            );
            foreach ($attributes as $attribute) {
                $this->rules[$name]->addRule($attribute->newInstance());
            }
        }
    }

    /**
     * Get rules for a specific property.
     */
    protected function getRuleset(string $property_name): Ruleset
    {
        // Cache rules.
        if (!$this->rulesAreCached) {
            $this->cacheRules();
        }

        return $this->rules[$property_name];
    }
}
