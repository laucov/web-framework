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

namespace Tests\Security;

use Laucov\WebFwk\Config\Authorization;
use Laucov\WebFwk\Config\Database;
use Laucov\WebFwk\Config\Session;
use Laucov\WebFwk\Providers\ConfigProvider;
use Laucov\WebFwk\Providers\ServiceProvider;
use Laucov\WebFwk\Security\AccreditationResult;
use Laucov\WebFwk\Security\Authentication\AuthnCancelResult;
use Laucov\WebFwk\Security\Authentication\AuthnRequestResult;
use Laucov\WebFwk\Security\Authentication\AuthnResult;
use Laucov\WebFwk\Security\Authentication\Interfaces\AuthnFactoryInterface;
use Laucov\WebFwk\Security\Authentication\Interfaces\AuthnInterface;
use Laucov\WebFwk\Security\Authorizer;
use Laucov\WebFwk\Security\UserStatus;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Security\Authorizer
 */
class AuthorizerTest extends TestCase
{
    protected Authorizer $authorizer;

    protected ConfigProvider $config;

    protected ServiceProvider $services;

    private string $sessionPath = __DIR__ . '/session-files';

    /**
     * Provides callbacks to execute before getting authentication options.
     * 
     * The getter must throw an exception.
     */
    public function authnOptionsGetterInitProvider(): array
    {
        return [
            [function (Authorizer $authz, ServiceProvider $services): void {
                // Don't create a session.
            }],
            [function (Authorizer $authz, ServiceProvider $services): void {
                // Create session, but don't login.
                $id = $services->session()->createSession()->id;
                $authz->setSession($id);
            }],
            [function (Authorizer $authz, ServiceProvider $services): void {
                // Create session and login.
                $id = $services->session()->createSession()->id;
                $authz->setSession($id);
                $authz->accredit('john', '1234');
            }],
            [function (Authorizer $authz, ServiceProvider $services): void {
                $id = $services->session()->createSession()->id;
                $authz->setSession($id);
                $authz->accredit('mary', '4321');
                $authz->requestAuthn('1');
            }],
            [function (Authorizer $authz, ServiceProvider $services): void {
                $id = $services->session()->createSession()->id;
                $authz->setSession($id);
                $authz->accredit('mary', '4321');
                $authz->requestAuthn('1');
                $data = ['value' => 4];
                $authz->authenticate($data);
            }],
        ];
    }

    /**
     * Provides callbacks to execute before getting the current authentication.
     * 
     * The getter must throw an exception.
     */
    public function currentAuthnGetterInitProvider(): array
    {
        return [
            [function (Authorizer $authz, ServiceProvider $services): void {
                // Don't create a session.
            }],
            [function (Authorizer $authz, ServiceProvider $services): void {
                // Create session, but don't login.
                $id = $services->session()->createSession()->id;
                $authz->setSession($id);
            }],
            [function (Authorizer $authz, ServiceProvider $services): void {
                // Create session and login.
                $id = $services->session()->createSession()->id;
                $authz->setSession($id);
                $authz->accredit('john', '1234');
            }],
            [function (Authorizer $authz, ServiceProvider $services): void {
                $id = $services->session()->createSession()->id;
                $authz->setSession($id);
                $authz->accredit('mary', '4321');
            }],
            [function (Authorizer $authz, ServiceProvider $services): void {
                $id = $services->session()->createSession()->id;
                $authz->setSession($id);
                $authz->accredit('mary', '4321');
                $authz->requestAuthn('1');
                $data = ['value' => 4];
                $authz->authenticate($data);
            }],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::accredit
     * @covers ::authenticate
     * @covers ::cancelAuthn
     * @covers ::getAuthentication
     * @covers ::getAuthnOptions
     * @covers ::getCurrentAuthn
     * @covers ::getUser
     * @covers ::getStatus
     * @covers ::logout
     * @covers ::requestAuthn
     * @covers ::setSession
     * @uses Laucov\WebFwk\Entities\User::testPassword
     * @uses Laucov\Modeling\Entity\AbstractEntity::__construct
     * @uses Laucov\Modeling\Entity\AbstractEntity::__set
     * @uses Laucov\Modeling\Model\AbstractModel::__construct
     * @uses Laucov\Modeling\Model\AbstractModel::applyDeletionFilter
     * @uses Laucov\Modeling\Model\AbstractModel::getEntities
     * @uses Laucov\Modeling\Model\AbstractModel::getEntity
     * @uses Laucov\Modeling\Model\AbstractModel::retrieve
     * @uses Laucov\WebFwk\Entities\UserAuthnMethod::getSettings
     * @uses Laucov\WebFwk\Models\UserAuthnMethodModel::listForUser
     * @uses Laucov\WebFwk\Models\UserAuthnMethodModel::retrieveForUser
     * @uses Laucov\WebFwk\Models\UserModel::retrieveWithLogin
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ConfigProvider::addConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::createInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getName
     * @uses Laucov\WebFwk\Providers\ConfigProvider::hasConfig
     * @uses Laucov\WebFwk\Providers\EnvMatch::__construct
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::getValue
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::hasDependency
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::setConfigProvider
     * @uses Laucov\WebFwk\Providers\ServiceProvider::__construct
     * @uses Laucov\WebFwk\Providers\ServiceProvider::db
     * @uses Laucov\WebFwk\Providers\ServiceProvider::getService
     * @uses Laucov\WebFwk\Providers\ServiceProvider::session
     * @uses Laucov\WebFwk\Security\Authentication\AuthnFactory::__construct
     * @uses Laucov\WebFwk\Services\DatabaseService::__construct
     * @uses Laucov\WebFwk\Services\DatabaseService::createConnection
     * @uses Laucov\WebFwk\Services\DatabaseService::getConnection
     * @uses Laucov\WebFwk\Services\FileSessionService::__construct
     * @uses Laucov\WebFwk\Services\FileSessionService::createSession
     * @uses Laucov\WebFwk\Services\FileSessionService::getSession
     * @uses Laucov\WebFwk\Services\FileSessionService::validateId
     */
    public function testCanAuthorize(): void
    {
        // Ensure no session is active.
        $this->assertSame(
            UserStatus::NO_ACTIVE_SESSION,
            $this->authorizer->getStatus(),
            'Assert that status is UserStatus::NO_ACTIVE_SESSION',
        );

        // Ensure cannot perform authorization ops without setting a session.
        $this->assertSame(
            AccreditationResult::NO_ACTIVE_SESSION,
            $this->authorizer->accredit('fulano', '987654321'),
            'Assert that result is AccreditationResult::NO_ACTIVE_SESSION',
        );
        $this->assertSame(
            AuthnRequestResult::NO_ACTIVE_SESSION,
            $this->authorizer->requestAuthn('2'),
            'Assert that result is AuthnRequestResult::NO_ACTIVE_SESSION',
        );
        $this->assertSame(
            AuthnCancelResult::NO_ACTIVE_SESSION,
            $this->authorizer->cancelAuthn(),
            'Assert that result is AuthnCancelResult::NO_ACTIVE_SESSION',
        );
        $this->assertSame(
            AuthnResult::NO_ACTIVE_SESSION,
            $this->authorizer->authenticate([]),
            'Assert that result is AuthnResult::NO_ACTIVE_SESSION',
        );

        // Set session ID.
        $id = $this->services->session()->createSession()->id;
        $this->authorizer->setSession($id);

        // Ensure the session does not have an user ID.
        $this->assertSame(
            UserStatus::NO_ACCREDITED_USER,
            $this->authorizer->getStatus(),
            'Assert that status is UserStatus::NOT_LOGGED_IN',
        );

        // Ensure cannot authenticate without an user ID.
        $this->assertSame(
            AuthnRequestResult::NO_ACCREDITED_USER,
            $this->authorizer->requestAuthn('2'),
            'Assert that result is AuthnRequestResult::NO_ACCREDITED_USER',
        );
        $this->assertSame(
            AuthnCancelResult::NO_ACCREDITED_USER,
            $this->authorizer->cancelAuthn(),
            'Assert that result is AuthnCancelResult::NO_ACCREDITED_USER',
        );
        $this->assertSame(
            AuthnResult::NO_ACCREDITED_USER,
            $this->authorizer->authenticate([]),
            'Assert that result is AuthnResult::NO_ACCREDITED_USER',
        );

        // Ensure that we do not have a user object yet.
        $this->assertNull($this->authorizer->getUser());

        // Test accreditation with invalid login.
        $this->assertSame(
            AccreditationResult::WRONG_LOGIN,
            $this->authorizer->accredit('joao', '1234'),
            'Assert that result is AccreditationResult::WRONG_LOGIN',
        );
        // Test accreditation with invalid password.
        $this->assertSame(
            AccreditationResult::WRONG_PASSWORD,
            $this->authorizer->accredit('john', '1111'),
            'Assert that result is AccreditationResult::WRONG_PASSWORD',
        );

        // Test accreditation with valid credentials.
        $this->assertSame(
            AccreditationResult::SUCCESS,
            $this->authorizer->accredit('john', '1234'),
            'Assert that result is AccreditationResult::SUCCESS',
        );
        $this->assertSame(
            UserStatus::ACCREDITED,
            $this->authorizer->getStatus(),
            'Assert that status is UserStatus::ACCREDITED',
        );

        // Get user.
        $user = $this->authorizer->getUser();
        $this->assertSame(1, $user->id);
        $this->assertSame('john', $user->login);

        // Test accreditation with MFA.
        $this->assertSame(
            AccreditationResult::SUCCESS,
            $this->authorizer->accredit('mary', '4321'),
            'Assert that result is AccreditationResult::SUCCESS',
        );
        $this->assertSame(
            UserStatus::AWAITING_AUTHENTICATION,
            $this->authorizer->getStatus(),
            'Assert that status is UserStatus::AWAITING_AUTHENTICATION',
        );

        // Get user.
        $user = $this->authorizer->getUser();
        $this->assertSame(2, $user->id);
        $this->assertSame('mary', $user->login);

        // Check available authentication methods.
        $authn_methods = $this->authorizer->getAuthnOptions();
        $this->assertIsArray($authn_methods);
        $this->assertCount(2, $authn_methods);
        $this->assertSame(1, $authn_methods[0]->id);
        $this->assertSame('foobar', $authn_methods[0]->name);
        $this->assertFalse($authn_methods[0]->completed);
        $this->assertSame(4, $authn_methods[1]->id);
        $this->assertSame('invalid', $authn_methods[1]->name);
        $this->assertFalse($authn_methods[1]->completed);

        // Try to authenticate without requesting the process.
        $data = ['value' => 0];
        $this->assertSame(
            AuthnResult::NOT_REQUESTED,
            $this->authorizer->authenticate($data),
            'Assert that result is AuthnResult::NOT_REQUESTED',
        );

        // Request authentication that is not registered for this user.
        $this->assertSame(
            AuthnRequestResult::NOT_FOUND,
            $this->authorizer->requestAuthn('2'),
            'Assert that result is AuthnRequestResult::NOT_FOUND',
        );

        // Request inexistent but registered authentication method.
        $this->assertSame(
            AuthnRequestResult::INVALID_METHOD,
            $this->authorizer->requestAuthn('4'),
            'Assert that result is AuthnRequestResult::INVALID',
        );

        // Request existing authentication.
        $this->assertSame(
            AuthnRequestResult::REQUESTED,
            $this->authorizer->requestAuthn('1'),
            'Assert that result is AuthnRequestResult::REQUESTED',
        );

        // Test invalid data.
        $this->assertSame(
            AuthnResult::FAILURE,
            $this->authorizer->authenticate($data),
            'Assert that result is AuthnResult::FAILURE',
        );

        // Test valid data.
        $data['value'] = 4;
        $this->assertSame(
            AuthnResult::SUCCESS,
            $this->authorizer->authenticate($data),
            'Assert that result is AuthnResult::SUCCESS',
        );

        // Should be authenticated (has only 1 required step).
        $this->assertSame(
            UserStatus::AUTHENTICATED,
            $this->authorizer->getStatus(),
            'Assert that status is UserStatus::AUTHENTICATED',
        );

        // Test user with multiple authentication steps.
        $this->assertSame(
            AccreditationResult::SUCCESS,
            $this->authorizer->accredit('michael', 'abcd'),
            'Assert that result is AccreditationResult::SUCCESS',
        );
        $this->assertSame(
            UserStatus::AWAITING_AUTHENTICATION,
            $this->authorizer->getStatus(),
            'Assert that status is UserStatus::AWAITING_AUTHENTICATION',
        );

        // Check available authentication methods.
        $authn_methods = $this->authorizer->getAuthnOptions();
        $this->assertIsArray($authn_methods);
        $this->assertCount(2, $authn_methods);
        $this->assertSame(2, $authn_methods[0]->id);
        $this->assertSame('foobar', $authn_methods[0]->name);
        $this->assertFalse($authn_methods[0]->completed);
        $this->assertSame(3, $authn_methods[1]->id);
        $this->assertSame('baz', $authn_methods[1]->name);
        $this->assertFalse($authn_methods[1]->completed);

        // Request 1st authentication.
        $this->assertSame(
            AuthnRequestResult::REQUESTED,
            $this->authorizer->requestAuthn('2'),
            'Assert that result is AuthnRequestResult::REQUESTED',
        );

        // Check status.
        $this->assertSame(
            UserStatus::AUTHENTICATING,
            $this->authorizer->getStatus(),
            'Assert that result is UserStatus::AUTHENTICATING',
        );

        // Test giving up.
        $this->assertSame(
            AuthnCancelResult::SUCCESS,
            $this->authorizer->cancelAuthn(),
            'Assert that result is AuthnCancelResult::SUCCESS',
        );

        // Check status.
        $this->assertSame(
            UserStatus::AWAITING_AUTHENTICATION,
            $this->authorizer->getStatus(),
            'Assert that result is UserStatus::AUTHENTICATING',
        );

        // Request authentication again.
        $this->assertSame(
            AuthnRequestResult::REQUESTED,
            $this->authorizer->requestAuthn('2'),
            'Assert that result is AuthnRequestResult::REQUESTED',
        );

        // Check status.
        $this->assertSame(
            UserStatus::AUTHENTICATING,
            $this->authorizer->getStatus(),
            'Assert that result is UserStatus::AUTHENTICATING',
        );

        // Get authentication method name.
        $current = $this->authorizer->getCurrentAuthn();
        $this->assertSame('foobar', $current->name);
        $this->assertIsArray($current->fields);

        // Complete 1st authentication.
        $this->assertSame(
            AuthnResult::SUCCESS,
            $this->authorizer->authenticate(['value' => 6]),
            'Assert that result is AuthnResult::SUCCESS',
        );

        // Ensure still needs other method.
        $this->assertSame(
            UserStatus::AWAITING_AUTHENTICATION,
            $this->authorizer->getStatus(),
            'Assert that status is UserStatus::AWAITING_AUTHENTICATION',
        );

        // Check available authentication methods.
        $authn_methods = $this->authorizer->getAuthnOptions();
        $this->assertIsArray($authn_methods);
        $this->assertCount(2, $authn_methods);
        $this->assertSame(2, $authn_methods[0]->id);
        $this->assertTrue($authn_methods[0]->completed);
        $this->assertSame(3, $authn_methods[1]->id);
        $this->assertFalse($authn_methods[1]->completed);

        // Check if resets the current authentication method.
        $this->assertSame(
            AuthnResult::NOT_REQUESTED,
            $this->authorizer->authenticate([]),
            'Assert that result is AuthnResult::NOT_REQUESTED',
        );

        // Ensure can't repeat a method.
        $this->assertSame(
            AuthnRequestResult::ALREADY_COMPLETED,
            $this->authorizer->requestAuthn('2'),
            'Assert that result is AuthnRequestResult::ALREADY_COMPLETED',
        );

        // Request 2nd authentication.
        $this->assertSame(
            AuthnRequestResult::REQUESTED,
            $this->authorizer->requestAuthn('3'),
            'Assert that result is AuthnRequestResult::REQUESTED',
        );

        // Complete 2nd authentication.
        $this->assertSame(
            AuthnResult::SUCCESS,
            $this->authorizer->authenticate(['value' => 12]),
            'Assert that result is AuthnResult::SUCCESS',
        );

        // Should be authenticated (has only 1 required step).
        $this->assertSame(
            UserStatus::AUTHENTICATED,
            $this->authorizer->getStatus(),
            'Assert that status is UserStatus::AUTHENTICATED',
        );

        // Create a callable to get the authorizer session.
        $get_session = function (): null|\Laucov\Sessions\Session {
            $property = new \ReflectionProperty($this->authorizer, 'session');
            return $property->getValue($this->authorizer);
        };

        // Get the current session ID.
        $prev_session = $get_session();
        $this->assertNotNull($prev_session);
        $prev_session_id = $prev_session->id;

        // Logout without destroying the session.
        $this->authorizer->logout();
        $this->assertSame(
            UserStatus::NO_ACCREDITED_USER,
            $this->authorizer->getStatus(),
            'Assert that status is UserStatus::NO_ACCREDITED_USER',
        );

        // Assert that the session wasn't destroyed.
        $curr_session = $get_session();
        $this->assertSame($prev_session, $curr_session);
        // Assert that the session ID wasn't changed.
        $this->assertSame($prev_session_id, $curr_session->id);

        // Assert that the user was removed.
        $this->assertNull($this->authorizer->getUser());

        // Login without changing the session ID.
        $this->assertSame(
            AccreditationResult::SUCCESS,
            $this->authorizer->accredit('john', '1234'),
            'Assert that result is AccreditationResult::SUCCESS',
        );
        $this->assertSame(
            UserStatus::ACCREDITED,
            $this->authorizer->getStatus(),
            'Assert that status is UserStatus::ACCREDITED',
        );

        // Assert that the session wasn't destroyed.
        $curr_session = $get_session();
        $this->assertSame($prev_session, $curr_session);
        // Assert that the session ID wasn't changed.
        $this->assertSame($prev_session_id, $curr_session->id);

        // Assert that the session file exists.
        $session_filename = "{$this->sessionPath}/{$prev_session_id}";
        $this->assertFileExists($session_filename);

        // Logout and destroy the session.
        $this->authorizer->logout(true);
        $this->assertSame(
            AccreditationResult::NO_ACTIVE_SESSION,
            $this->authorizer->accredit('john', '1234'),
            'Assert that result is AccreditationResult::NO_ACTIVE_SESSION',
        );

        // Assert that the session was destroyed.
        $this->assertNull($get_session());
        // Assert that the session ID was removed.
        $this->assertSame('', $prev_session->id);
        // Assert that the session file was also removed.
        $this->assertFileDoesNotExist($session_filename);

        // Assert that the user was removed.
        $this->assertNull($this->authorizer->getUser());
    }

    /**
     * @covers ::authenticate
     * @covers ::getCurrentAuthn
     * @uses Laucov\WebFwk\Entities\User::testPassword
     * @uses Laucov\Modeling\Entity\AbstractEntity::__construct
     * @uses Laucov\Modeling\Entity\AbstractEntity::__set
     * @uses Laucov\Modeling\Model\AbstractModel::__construct
     * @uses Laucov\Modeling\Model\AbstractModel::applyDeletionFilter
     * @uses Laucov\Modeling\Model\AbstractModel::getEntities
     * @uses Laucov\Modeling\Model\AbstractModel::getEntity
     * @uses Laucov\Modeling\Model\AbstractModel::retrieve
     * @uses Laucov\WebFwk\Models\UserAuthnMethodModel::retrieveForUser
     * @uses Laucov\WebFwk\Models\UserModel::retrieveWithLogin
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ConfigProvider::addConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::createInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getName
     * @uses Laucov\WebFwk\Providers\ConfigProvider::hasConfig
     * @uses Laucov\WebFwk\Providers\EnvMatch::__construct
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::getValue
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::hasDependency
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::setConfigProvider
     * @uses Laucov\WebFwk\Providers\ServiceProvider::__construct
     * @uses Laucov\WebFwk\Providers\ServiceProvider::db
     * @uses Laucov\WebFwk\Providers\ServiceProvider::getService
     * @uses Laucov\WebFwk\Providers\ServiceProvider::session
     * @uses Laucov\WebFwk\Security\Authorizer::__construct
     * @uses Laucov\WebFwk\Security\Authorizer::accredit
     * @uses Laucov\WebFwk\Security\Authorizer::getAuthentication
     * @uses Laucov\WebFwk\Security\Authorizer::getStatus
     * @uses Laucov\WebFwk\Security\Authorizer::requestAuthn
     * @uses Laucov\WebFwk\Security\Authorizer::setSession
     * @uses Laucov\WebFwk\Services\DatabaseService::__construct
     * @uses Laucov\WebFwk\Services\DatabaseService::createConnection
     * @uses Laucov\WebFwk\Services\DatabaseService::getConnection
     * @uses Laucov\WebFwk\Services\DatabaseService::getTable
     * @uses Laucov\WebFwk\Services\FileSessionService::__construct
     * @uses Laucov\WebFwk\Services\FileSessionService::createSession
     * @uses Laucov\WebFwk\Services\FileSessionService::getSession
     * @uses Laucov\WebFwk\Services\FileSessionService::validateId
     */
    public function testInformsUnusualFailures(): void
    {
        // Request valid authentication.
        $session_id = $this->services->session()->createSession()->id;
        $this->authorizer->setSession($session_id);
        $this->authorizer->accredit('michael', 'abcd');
        $this->authorizer->requestAuthn('2');

        // Remove user authentication from database before completing it.
        // Could happen between requests.
        $this->services
            ->db()
            ->getTable('users_authn_methods')
            ->filter('id', '=', '2')
            ->deleteRecords();

        // Try to get the current authentication method - should fail.
        $this->assertNull($this->authorizer->getCurrentAuthn());

        // Try to complete - should fail.
        $this->assertSame(
            AuthnResult::NOT_FOUND,
            $this->authorizer->authenticate(['value' => 6]),
            'Assert that result is AuthnResult::NOT_FOUND',
        );

        // Request another valid authetication.
        $this->authorizer->requestAuthn('3');
        $this->authorizer->setSession(null);

        // Change authentication factory.
        // Could happen between requests.
        $authz = $this->config->getConfig(Authorization::class);
        $authz->authnFactory = UselessAuthnFactory::class;
        $this->authorizer = new Authorizer($authz, $this->services);
        $this->authorizer->setSession($session_id);

        // Try to get the current authentication method - should fail.
        $this->assertNull($this->authorizer->getCurrentAuthn());

        // Try to complete - should fail.
        $this->assertSame(
            AuthnResult::INVALID_METHOD,
            $this->authorizer->authenticate(['value' => 12]),
            'Assert that result is AuthnResult::INVALID_METHOD',
        );
    }

    /**
     * @covers ::getCurrentAuthn
     * @uses Laucov\WebFwk\Entities\User::testPassword
     * @uses Laucov\WebFwk\Models\UserModel::retrieveWithLogin
     * @uses Laucov\WebFwk\Models\UserAuthnMethodModel::retrieveForUser
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ConfigProvider::addConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::createInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getName
     * @uses Laucov\WebFwk\Providers\ConfigProvider::hasConfig
     * @uses Laucov\WebFwk\Providers\EnvMatch::__construct
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::getValue
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::hasDependency
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::setConfigProvider
     * @uses Laucov\WebFwk\Providers\ServiceProvider::__construct
     * @uses Laucov\WebFwk\Providers\ServiceProvider::db
     * @uses Laucov\WebFwk\Providers\ServiceProvider::getService
     * @uses Laucov\WebFwk\Providers\ServiceProvider::session
     * @uses Laucov\WebFwk\Security\Authorizer::__construct
     * @uses Laucov\WebFwk\Security\Authorizer::accredit
     * @uses Laucov\WebFwk\Security\Authorizer::authenticate
     * @uses Laucov\WebFwk\Security\Authorizer::getAuthentication
     * @uses Laucov\WebFwk\Security\Authorizer::getStatus
     * @uses Laucov\WebFwk\Security\Authorizer::requestAuthn
     * @uses Laucov\WebFwk\Security\Authorizer::setSession
     * @uses Laucov\WebFwk\Services\DatabaseService::__construct
     * @uses Laucov\WebFwk\Services\DatabaseService::createConnection
     * @uses Laucov\WebFwk\Services\DatabaseService::getConnection
     * @uses Laucov\WebFwk\Services\FileSessionService::__construct
     * @uses Laucov\WebFwk\Services\FileSessionService::createSession
     * @uses Laucov\WebFwk\Services\FileSessionService::getSession
     * @uses Laucov\WebFwk\Services\FileSessionService::validateId
     * @dataProvider currentAuthnGetterInitProvider
     */
    public function testMustBeAuthenticatingToGetCurrentMethod(
        callable $callable,
    ): void {
        $callable($this->authorizer, $this->services);
        $this->expectException(\RuntimeException::class);
        $this->authorizer->getCurrentAuthn();
    }

    /**
     * @covers ::getAuthnOptions
     * @uses Laucov\WebFwk\Entities\User::testPassword
     * @uses Laucov\WebFwk\Models\UserAuthnMethodModel::retrieveForUser
     * @uses Laucov\WebFwk\Models\UserModel::retrieveWithLogin
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ConfigProvider::addConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::createInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getName
     * @uses Laucov\WebFwk\Providers\ConfigProvider::hasConfig
     * @uses Laucov\WebFwk\Providers\EnvMatch::__construct
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::getValue
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::hasDependency
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::setConfigProvider
     * @uses Laucov\WebFwk\Providers\ServiceProvider::__construct
     * @uses Laucov\WebFwk\Providers\ServiceProvider::db
     * @uses Laucov\WebFwk\Providers\ServiceProvider::getService
     * @uses Laucov\WebFwk\Providers\ServiceProvider::session
     * @uses Laucov\WebFwk\Security\Authorizer::__construct
     * @uses Laucov\WebFwk\Security\Authorizer::accredit
     * @uses Laucov\WebFwk\Security\Authorizer::authenticate
     * @uses Laucov\WebFwk\Security\Authorizer::getAuthentication
     * @uses Laucov\WebFwk\Security\Authorizer::getStatus
     * @uses Laucov\WebFwk\Security\Authorizer::requestAuthn
     * @uses Laucov\WebFwk\Security\Authorizer::setSession
     * @uses Laucov\WebFwk\Services\DatabaseService::__construct
     * @uses Laucov\WebFwk\Services\DatabaseService::createConnection
     * @uses Laucov\WebFwk\Services\DatabaseService::getConnection
     * @uses Laucov\WebFwk\Services\FileSessionService::__construct
     * @uses Laucov\WebFwk\Services\FileSessionService::createSession
     * @uses Laucov\WebFwk\Services\FileSessionService::getSession
     * @uses Laucov\WebFwk\Services\FileSessionService::validateId
     * @dataProvider authnOptionsGetterInitProvider
     */
    public function testMustBeAwaitingAuthenticationToGetOptions(
        callable $callable
    ): void {
        $callable($this->authorizer, $this->services);
        $this->expectException(\RuntimeException::class);
        $this->authorizer->getAuthnOptions();
    }

    /**
     * @covers ::setSession
     * @uses Laucov\WebFwk\Entities\User::testPassword
     * @uses Laucov\Modeling\Entity\AbstractEntity::__construct
     * @uses Laucov\Modeling\Entity\AbstractEntity::__set
     * @uses Laucov\Modeling\Model\AbstractModel::__construct
     * @uses Laucov\Modeling\Model\AbstractModel::applyDeletionFilter
     * @uses Laucov\Modeling\Model\AbstractModel::getEntities
     * @uses Laucov\Modeling\Model\AbstractModel::getEntity
     * @uses Laucov\Modeling\Model\AbstractModel::retrieve
     * @uses Laucov\WebFwk\Models\UserModel::retrieveWithLogin
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ConfigProvider::addConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::createInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getName
     * @uses Laucov\WebFwk\Providers\ConfigProvider::hasConfig
     * @uses Laucov\WebFwk\Providers\EnvMatch::__construct
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::getValue
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::hasDependency
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::setConfigProvider
     * @uses Laucov\WebFwk\Providers\ServiceProvider::__construct
     * @uses Laucov\WebFwk\Providers\ServiceProvider::db
     * @uses Laucov\WebFwk\Providers\ServiceProvider::getService
     * @uses Laucov\WebFwk\Providers\ServiceProvider::session
     * @uses Laucov\WebFwk\Security\Authorizer::__construct
     * @uses Laucov\WebFwk\Security\Authorizer::accredit
     * @uses Laucov\WebFwk\Security\Authorizer::getStatus
     * @uses Laucov\WebFwk\Services\DatabaseService::__construct
     * @uses Laucov\WebFwk\Services\DatabaseService::createConnection
     * @uses Laucov\WebFwk\Services\DatabaseService::getConnection
     * @uses Laucov\WebFwk\Services\FileSessionService::__construct
     * @uses Laucov\WebFwk\Services\FileSessionService::createSession
     * @uses Laucov\WebFwk\Services\FileSessionService::getSession
     * @uses Laucov\WebFwk\Services\FileSessionService::validateId
     */
    public function testSavesUserData(): void
    {
        // Set session IDs.
        $id_a = $this->services->session()->createSession()->id;
        $id_b = $this->services->session()->createSession()->id;

        // Accredit first session.
        $this->authorizer->setSession($id_a);
        $this->authorizer->accredit('john', '1234');
        $this->assertSame(
            UserStatus::ACCREDITED,
            $this->authorizer->getStatus(),
            'Assert that status is UserStatus::ACCREDITED',
        );

        // Remove session.
        $this->authorizer->setSession(null);
        $this->assertSame(
            UserStatus::NO_ACTIVE_SESSION,
            $this->authorizer->getStatus(),
            'Assert that status is UserStatus::NO_ACTIVE_SESSION',
        );

        // Change session.
        $this->authorizer->setSession($id_b);
        $this->assertSame(
            UserStatus::NO_ACCREDITED_USER,
            $this->authorizer->getStatus(),
            'Assert that status is UserStatus::NOT_LOGGED_IN',
        );

        // Change back to accredited session.
        $this->authorizer->setSession($id_a);
        $this->assertSame(
            UserStatus::ACCREDITED,
            $this->authorizer->getStatus(),
            'Assert that status is UserStatus::ACCREDITED',
        );
    }

    /**
     * @covers ::setSession
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ConfigProvider::addConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::createInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getName
     * @uses Laucov\WebFwk\Providers\ConfigProvider::hasConfig
     * @uses Laucov\WebFwk\Providers\EnvMatch::__construct
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::getValue
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::hasDependency
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::setConfigProvider
     * @uses Laucov\WebFwk\Providers\ServiceProvider::__construct
     * @uses Laucov\WebFwk\Providers\ServiceProvider::db
     * @uses Laucov\WebFwk\Providers\ServiceProvider::getService
     * @uses Laucov\WebFwk\Providers\ServiceProvider::session
     * @uses Laucov\WebFwk\Security\Authorizer::__construct
     * @uses Laucov\WebFwk\Services\DatabaseService::__construct
     * @uses Laucov\WebFwk\Services\DatabaseService::createConnection
     * @uses Laucov\WebFwk\Services\DatabaseService::getConnection
     * @uses Laucov\WebFwk\Services\FileSessionService::__construct
     * @uses Laucov\WebFwk\Services\FileSessionService::validateId
     */
    public function testValidatesSessionIds(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->authorizer->setSession('invalid_session_id');
    }

    protected function setUp(): void
    {
        // Ensure the session file directory exists.
        if (!is_dir($this->sessionPath)) {
            mkdir($this->sessionPath);
        }

        // Create configuration provider instance.
        $this->config = new ConfigProvider([]);
        $this->config->addConfig(Authorization::class);
        $this->config->addConfig(Database::class);
        $this->config->addConfig(Session::class);

        // Adjust authorization config.
        $authz = $this->config->getConfig(Authorization::class);
        $authz->authnFactory = OtherAuthnFactory::class;

        // Adjust database config.
        $database = $this->config->getConfig(Database::class);
        $database->defaultConnections['sqlite'] = ['sqlite::memory:'];
        $database->defaultConnection = 'sqlite';

        // Adjust session config.
        $session = $this->config->getConfig(Session::class);
        $session->path = $this->sessionPath;

        // Create service provider instance.
        $this->services = new ServiceProvider($this->config);

        // Create passwords.
        $pass_a = '$2y$10$r9h/OqWhmUcHgry5stBWI.69AKtcm6kNUJ0nn16VnKo8fyp7T1OM2';
        $pass_b = '$2y$10$08oGKnASGV6uwgohcFvIEuc410qRYfo5GiWVpQm9lO8Z30/zR86DC';
        $pass_c = '$2y$10$XQtB8gUT20isJWm8hLvnE.jbfmZMZI5zLFrJpTDRwGeCbfOCVctrO';

        // Create tables.
        $conn = $this->services->db()->getConnection();
        $conn
            ->query(<<<SQL
                CREATE TABLE "users" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                    "login" VARCHAR(64),
                    "password_hash" VARCHAR(256),
                    "authentication_steps" INT(11),
                    "deleted_at" DATETIME
                )
                SQL)
            ->query(<<<SQL
                CREATE TABLE "users_authn_methods" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
                    "user_id" INT(11),
                    "name" VARCHAR(32),
                    "settings" VARCHAR(512),
                    "deleted_at" DATETIME
                )
                SQL)
            ->query(<<<SQL
                INSERT INTO "users"
                    ("login", "password_hash", "authentication_steps")
                VALUES
                    ('john', '{$pass_a}', 0),
                    ('mary', '{$pass_b}', 1),
                    ('michael', '{$pass_c}', 2)
                SQL)
            ->query(<<<SQL
                INSERT INTO "users_authn_methods"
                    ("user_id", "name", "settings")
                VALUES
                    (2, 'foobar', '{"factor":2}'),
                    (3, 'foobar', '{"factor":3}'),
                    (3, 'baz', '{"factor":4}'),
                    (2, 'invalid', '{"some":"config"}')
                SQL);

        // Create authorizer instance.
        $this->authorizer = new Authorizer($authz, $this->services);
    }

    protected function tearDown(): void
    {
        // Remove session files.
        $dir = $this->sessionPath;
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $filename = "{$dir}/{$item}";
            if (!is_dir($filename)) {
                unlink($filename);
            }
        }
    }
}

class UselessAuthnFactory implements AuthnFactoryInterface
{
    public function __construct(protected ServiceProvider $services)
    {
    }
}

class OtherAuthnFactory extends UselessAuthnFactory
{
    public function foobar(): FoobarAuthn
    {
        return new FoobarAuthn();
    }

    public function baz(): FoobarAuthn
    {
        return new BazAuthn();
    }
}

class FoobarAuthn implements AuthnInterface
{
    protected static null|int $code = 0;

    protected int $factor;

    public function configure(array $data): void
    {
        $this->factor = (int) $data['factor'];
    }

    public function getFields(): array
    {
        return [];
    }

    public function request(): void
    {
        static::$code = 2 * $this->factor;
    }

    public function validate(array $data): bool
    {
        return static::$code !== null
            && isset($data['value'])
            && $data['value'] === static::$code;
    }
}

class BazAuthn extends FoobarAuthn
{
    public function request(): void
    {
        static::$code = 3 * $this->factor;
    }
}
