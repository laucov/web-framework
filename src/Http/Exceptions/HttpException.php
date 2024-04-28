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

namespace Laucov\WebFwk\Http\Exceptions;

/**
 * Represents an HTTP error message.
 */
class HttpException extends \RuntimeException
{
    /**
     * Create the exception instance.
     */
    public function __construct(
        string $message = '',
        int $code = 400,
        \Throwable $previous = null,
    ) {
        // Validate status code.
        if ($code < 100 || $code > 599) {
            $message = 'Status codes must be within the range of 100 to 599.';
            throw new \InvalidArgumentException($message);
        }

        // Set message an call the parent constructor.
        parent::__construct($message, $code, $previous);
    }
}
