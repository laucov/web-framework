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

    /**
     * @covers ::__construct
     * @covers ::accredit
     * @covers ::authenticate
     * @covers ::getAuthentication
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
            AuthnResult::NO_ACCREDITED_USER,
            $this->authorizer->authenticate([]),
            'Assert that result is AuthnResult::NO_ACCREDITED_USER',
        );

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

        // Request 1st authentication.
        $this->assertSame(
            AuthnRequestResult::REQUESTED,
            $this->authorizer->requestAuthn('2'),
            'Assert that result is AuthnRequestResult::REQUESTED',
        );

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
    }

    /**
     * @covers ::authenticate
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
     * @uses Laucov\WebFwk\Security\Authorizer::requestAuthn
     * @uses Laucov\WebFwk\Security\Authorizer::setSession
     * @uses Laucov\WebFwk\Services\DatabaseService::__construct
     * @uses Laucov\WebFwk\Services\DatabaseService::createConnection
     * @uses Laucov\WebFwk\Services\DatabaseService::getConnection
     * @uses Laucov\WebFwk\Services\DatabaseService::getTable
     * @uses Laucov\WebFwk\Services\FileSessionService::__construct
     * @uses Laucov\WebFwk\Services\FileSessionService::createSession
     * @uses Laucov\WebFwk\Services\FileSessionService::getSession
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

        // Try to complete - should fail.
        $this->assertSame(
            AuthnResult::INVALID_METHOD,
            $this->authorizer->authenticate(['value' => 12]),
            'Assert that result is AuthnResult::INVALID_METHOD',
        );
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

    protected function setUp(): void
    {
        // Ensure the session file directory exists.
        if (!is_dir(__DIR__ . '/session-files')) {
            mkdir(__DIR__ . '/session-files');
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
        $session->path = __DIR__ . '/session-files';

        // Create service provider instance.
        $this->services = new ServiceProvider($this->config);

        // Create passwords.
        $pass_a = password_hash('1234', PASSWORD_DEFAULT);
        $pass_b = password_hash('4321', PASSWORD_DEFAULT);
        $pass_c = password_hash('abcd', PASSWORD_DEFAULT);

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
        $dir = __DIR__ . '/session-files';
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
