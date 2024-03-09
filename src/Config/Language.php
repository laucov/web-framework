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

/**
 * Stores internationalization configuration.
 */
class Language implements ConfigInterface
{
    /**
     * Default locale.
     */
    public string $defaultLocale = 'en';

    /**
     * Initial language data.
     * 
     * Not recommended. Use language files instead.
     * 
     * @var array<string, array>
     */
    public array $data = [];

    /**
     * Language data files directories.
     * 
     * @var array<string>
     */
    public array $directories = [];

    /**
     * Locale redirects.
     * 
     * @var array<string, string>
     */
    public array $redirects = [];

    /**
     * Supported locales.
     * 
     * Note: the default locale is always supported.
     * 
     * @var array<string>
     */
    public array $supportedLocales = [];
}
