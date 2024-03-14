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

namespace Laucov\WebFramework\Config;

use Laucov\WebFramework\Config\Interfaces\ConfigInterface;
use Laucov\WebFramework\Security\Authentication\AuthnFactory;
use Laucov\WebFramework\Security\Authentication\Interfaces\AuthnFactoryInterface;

/**
 * Stores login and authentication parameters.
 */
class Authorization implements ConfigInterface
{
    /**
     * Authentication factory class name.
     * 
     * @var class-string<AuthnFactoryInterface>
     */
    public string $authnFactory = AuthnFactory::class;
}
