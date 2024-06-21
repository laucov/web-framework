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

namespace Laucov\WebFwk\Http\Traits;

use Laucov\Http\Cookie\ResponseCookie;
use Laucov\Http\Message\OutgoingResponse;
use Laucov\Http\Message\RequestInterface;
use Laucov\WebFwk\Config\Authorization;
use Laucov\WebFwk\Providers\ConfigProvider;
use Laucov\WebFwk\Providers\ServiceProvider;
use Laucov\WebFwk\Security\SessionGuard;

/**
 * Provides methods to easily control authorization dependant processes.
 * 
 * @property ConfigProvider $config
 * @property OutgoingResponse $response
 * @property ServiceProvider $services
 */
trait AuthzControllerTrait
{
    /**
     * Create a new `SessionGuard` instance using the request data.
     */
    protected function createSessionGuard(RequestInterface $request): SessionGuard
    {
        // Create instance.
        $config = $this->config->getConfig(Authorization::class);
        $authorizer = new SessionGuard($config, $this->services);

        // Get the session ID.
        $session_cookie = $request->getCookie('session_id');
        if ($session_cookie === null) {
            return $authorizer;
        }

        // Try to set a session.
        try {
            $authorizer->setSession($session_cookie->value);
        } catch (\RuntimeException $e) {
            $authorizer->setSession(null);
            $cookie = new ResponseCookie(
                name: 'session_id',
                value: '',
                expires: 'Thu, 01 Jan 1970 00:00:00 GMT',
            );
            $this->response->setCookie($cookie);
        }

        return $authorizer;
    }
}
