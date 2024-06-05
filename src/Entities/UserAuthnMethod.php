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

namespace Laucov\WebFwk\Entities;

use Laucov\Modeling\Entity\AbstractEntity;

/**
 * Represents a registered authentication method of a specific user.
 */
class UserAuthnMethod extends AbstractEntity
{
    /**
     * Record ID.
     */
    public int $id;

    /**
     * User ID.
     */
    public int $user_id;

    /**
     * Authentication method name.
     * 
     * Must match a method from the `AuthnFactoryInterface` object in use.
     * 
     * Used to get an `AbstractAuthn` object from the matching method.
     */
    public string $name;

    /**
     * Authentication method settings
     * 
     * Must be stored as a JSON string.
     */
    public string $settings;

    /**
     * Get the settings as an array.
     */
    public function getSettings(): array
    {
        // Parse JSON.
        $data = json_decode($this->settings, true);
        if (!is_array($data)) {
            $message = 'Could not parse the settings JSON into an array.';
            throw new \RuntimeException($message);
        }

        return $data;
    }

    /**
     * Set the settings from an array.
     */
    public function setSettings(array $settings): void
    {
        // Create JSON.
        $json = json_encode((object) $settings);
        if (!is_string($json)) {
            $message = 'Could not create a JSON string from the given array.';
            throw new \InvalidArgumentException($message);
        }

        // Store settings.
        $this->settings = $json;
    }
}
