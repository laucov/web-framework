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

use Covaleski\Otp\Totp;
use Laucov\WebFwk\Entities\TotpSettings;

/**
 * Provides authentication with time-based one-time passwords.
 * 
 * @extends AbstractAuthn<TotpSettings>
 */
class TotpAuthn extends AbstractAuthn
{
    /**
     * Settings entity class name.
     */
    protected string $settingsEntity = TotpSettings::class;

    /**
     * TOTP object.
     */
    protected Totp $totp;

    /**
     * Configure the process.
     */
    public function setup(): void
    {
        // Set the TOTP object.
        $this->totp = new Totp(
            $this->settings->digits,
            '',
            '',
            $this->settings->secret,
        );

        // Set offset and step.
        $this->totp
            ->setOffset($this->settings->offset)
            ->setStep($this->settings->step);
    }

    /**
     * Get the authentication expected fields.
     * 
     * @return array<AuthnField>
     */
    public function getFields(): array
    {
        // Create field.
        $field = new AuthnField();
        $field->name = 'password';
        $field->required = true;
        $field->type = 'text';

        return [$field];
    }

    /**
     * Start the process.
     */
    public function request(): void
    {
    }

    /**
     * Validate data for the process.
     */
    public function validate(array $data): bool
    {
        if (!isset($data['password'])) {
            return false;
        }

        return $this->totp->getPassword() === $data['password'];
    }
}
