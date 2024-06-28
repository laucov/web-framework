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

namespace Laucov\WebFwk\Security\Authentication;

use Laucov\Modeling\Entity\AbstractEntity;
use Laucov\Modeling\Entity\CreationResult;

/**
 * Controls the life cycle of a specific authentication process.
 * 
 * @template T of AbstractEntity
 */
abstract class AbstractAuthn
{
    /**
     * Get the authentication expected fields.
     * 
     * @return array<AuthnField>
     */
    abstract public function getFields(): array;

    /**
     * Start the process.
     */
    abstract public function request(): void;

    /**
     * Validate data for the process.
     */
    abstract public function validate(array $data): bool;

    /**
     * Setup the process after defining the settings.
     */
    abstract protected function setup(): void;

    /**
     * Settings entity class name.
     * 
     * @var class-string<T>
     */
    protected string $settingsEntity;

    /**
     * Current settings.
     * 
     * @var T
     */
    protected mixed $settings;

    /**
     * Create a settings object.
     * 
     * @return T
     */
    public function createSettings(): mixed
    {
        return new $this->settingsEntity();
    }

    /**
     * Create a settings object from array entries.
     * 
     * @return CreationResult<T>
     */
    public function createSettingsFromArray(array $data): mixed
    {
        return $this->settingsEntity::createFromArray($data);
    }

    /**
     * Configure the process.
     */
    public function configure(AbstractEntity $settings): void
    {
        // Assert that is a settings object.
        if (!is_a($settings, $this->settingsEntity)) {
            $message = 'The settings must be a %s object.';
            $message = sprintf($message, $this->settingsEntity);
            throw new \InvalidArgumentException($message);
        }

        // Use settings object and setup.
        $this->settings = $settings;
        $this->setup();
    }
}