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

namespace Laucov\WebFramework\Security\Authentication;

/**
 * Represents an user authentication request result.
 */
enum AuthnRequestResult
{
    /**
     * The authentication method was already requested and completed.
     * 
     * This method should not be requested again for the same session.
     */
    case ALREADY_COMPLETED;

    /**
     * The authentication method does not exist for the factory in use.
     */
    case INVALID_METHOD;
    
    /**
     * A session is active but there is no accredited user.
     */
    case NO_ACCREDITED_USER;

    /**
     * There is no active session to use.
     */
    case NO_ACTIVE_SESSION;

    /**
     * The authentication method was not found for the given user.
     */
    case NOT_FOUND;

    /**
     * The authentication process was successfully requested.
     */
    case REQUESTED;
}
