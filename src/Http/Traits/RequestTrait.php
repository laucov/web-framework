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

namespace Laucov\WebFramework\Http\Traits;

use Laucov\WebFramework\Data\ArrayReader;
use Laucov\WebFramework\Web\Uri;

/**
 * Has properties and methods common to request objects.
 */
trait RequestTrait
{
    /**
     * HTTP method.
     */
    protected string $method = 'GET';

    /**
     * URI object.
     */
    protected null|Uri $uri = null;

    /**
     * Get the request method.
     * 
     * Always returns the method name in uppercase characters.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get the URI object.
     */
    public function getUri(): Uri
    {
        if ($this->uri === null) {
            throw new \RuntimeException('Request URI is not defined.');
        }

        return $this->uri;
    }
}
