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

declare(strict_types=1);

namespace Tests\Unit\Http\Traits;

use Laucov\Http\Cookie\RequestCookie;
use Laucov\Http\Message\OutgoingResponse;
use Laucov\Http\Message\RequestInterface;
use Laucov\WebFwk\Config\Authorization;
use Laucov\WebFwk\Http\Traits\AuthzControllerTrait;
use Laucov\WebFwk\Providers\ConfigProvider;
use Laucov\WebFwk\Providers\ServiceProvider;
use Laucov\WebFwk\Security\SessionGuard;
use Laucov\WebFwk\Security\UserStatus;
use Laucov\WebFwk\Services\Interfaces\SessionServiceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Http\Traits\AuthzControllerTrait
 */
class AuthzControllerTraitTest extends TestCase
{
    /**
     * @covers ::createSessionGuard
     * @uses Laucov\WebFwk\Security\Authentication\AuthnFactory::__construct
     * @uses Laucov\WebFwk\Security\SessionGuard::__construct
     * @uses Laucov\WebFwk\Security\SessionGuard::getStatus
     * @uses Laucov\WebFwk\Security\SessionGuard::setSession
     */
    public function testCanGetTheSessionGuard(): void
    {
        // Mock dependencies.
        $authz_config = $this->createMock(Authorization::class);
        $config = $this->createMock(ConfigProvider::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(OutgoingResponse::class);
        $session_service = $this->createMock(SessionServiceInterface::class);
        $services = $this->createMock(ServiceProvider::class);

        // Create controller.
        $controller = new class () {
            use AuthzControllerTrait;
            public ConfigProvider $config;
            public OutgoingResponse $response;
            public ServiceProvider $services;
            public function test(RequestInterface $request): SessionGuard
            {
                return $this->createSessionGuard($request);
            }
        };

        // Set properties.
        $controller->config = $config;
        $controller->response = $response;
        $controller->services = $services;

        // Configure methods.
        $map = [[Authorization::class, $authz_config]];
        $config
            ->method('getConfig')
            ->will($this->returnValueMap($map));
        $services
            ->method('session')
            ->willReturn($session_service);

        // Test.
        $authorizer = $controller->test($request);
        $this->assertSame(
            UserStatus::NO_ACTIVE_SESSION,
            $authorizer->getStatus(),
        );

        // Configure methods to test session validity.
        $request_cookie = $this->createMock(RequestCookie::class);
        $request_cookie->value = 'some_string';
        $map = [['session_id', $request_cookie]];
        $request
            ->method('getCookie')
            ->will($this->returnValueMap($map));
        $session_service
            ->expects($this->once())
            ->method('getSession')
            ->with('some_string');
        $session_service
            ->expects($this->exactly(2))
            ->method('validateId')
            ->withConsecutive(['some_string'], ['some_string'])
            ->willReturn(true, false);

        // Test valid ID case.
        $authorizer = $controller->test($request);
        $this->assertSame(
            UserStatus::NO_ACCREDITED_USER,
            $authorizer->getStatus(),
        );

        // Test invalid ID case.
        $authorizer = $controller->test($request);
        $this->assertSame(
            UserStatus::NO_ACTIVE_SESSION,
            $authorizer->getStatus(),
        );
    }
}
