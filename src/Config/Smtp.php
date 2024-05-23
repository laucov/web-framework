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

namespace Laucov\WebFwk\Config;

use Laucov\WebFwk\Config\Interfaces\ConfigInterface;
use Laucov\WebFwk\Services\PhpMailerSmtpService;
use Laucov\WebFwk\Services\Interfaces\SmtpServiceInterface;

/**
 * Stores SMTP configuration.
 */
class Smtp implements ConfigInterface
{
    /**
     * Default "From" e-mail address.
     * 
     * The user login will be used if this field is `null`.
     */
    public null|string $fromAddress = null;

    /**
     * Default "From" mailbox name.
     */
    public null|string $fromName = null;

    /**
     * Host.
     */
    public string $host;

    /**
     * User password.
     */
    public string $password;

    /**
     * Port.
     */
    public int $port = 465;

    /**
     * Service class.
     * 
     * @var class-string<SmtpServiceInterface>
     */
    public string $service = PhpMailerSmtpService::class;

    /**
     * User login.
     */
    public string $user;
}