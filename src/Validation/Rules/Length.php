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

namespace Laucov\WebFwk\Validation\Rules;

use Laucov\WebFwk\Validation\Rules\Interfaces\RuleInterface;

/**
 * Requires a value to have a minimum and/or a maximum length.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Length implements RuleInterface
{
    /**
     * Create the rule instance.
     */
    public function __construct(
        /**
         * Minimum length.
         */
        public int $minimum = 0,

        /**
         * Maximum length.
         */
        public null|int $maximum = null,
    ) {
    }

    /**
     * Validate a single value.
     */
    public function validate(mixed $value): bool
    {
        if (!is_scalar($value)) {
            return false;
        }

        $length = mb_strlen($value, 'UTF-8');

        return $this->maximum !== null
            ? $length >= $this->minimum && $length <= $this->maximum
            : $length >= $this->minimum;
    }
}
