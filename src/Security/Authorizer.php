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

namespace Laucov\WebFwk\Security;

use Laucov\Sessions\Session;
use Laucov\WebFwk\Config\Authorization;
use Laucov\WebFwk\Entities\User;
use Laucov\WebFwk\Entities\UserAuthnMethod;
use Laucov\WebFwk\Models\UserAuthnMethodModel;
use Laucov\WebFwk\Models\UserModel;
use Laucov\WebFwk\Providers\ServiceProvider;
use Laucov\WebFwk\Security\Authentication\AuthnOption;
use Laucov\WebFwk\Security\Authentication\AuthnRequestResult;
use Laucov\WebFwk\Security\Authentication\AuthnResult;
use Laucov\WebFwk\Security\Authentication\Interfaces\AuthnFactoryInterface;
use Laucov\WebFwk\Security\Authentication\Interfaces\AuthnInterface;

/**
 * Accredits, authenticates and stores users in sessions.
 */
class Authorizer
{
    /**
     * Authentication factory.
     */
    protected AuthnFactoryInterface $authnFactory;

    /**
     * Session instance.
     */
    protected null|Session $session = null;

    /**
     * User instance.
     */
    protected null|User $user = null;

    /**
     * User model.
     */
    protected UserModel $userModel;

    /**
     * User authentication method model.
     */
    protected UserAuthnMethodModel $userAuthnMethodModel;

    /**
     * Create the authorizer instance.
     */
    public function __construct(
        /**
         * Authorization parameters.
         */
        protected Authorization $config,

        /**
         * Service provider.
         */
        protected ServiceProvider $services,
    ) {
        // Create the authorization factory.
        $authn_factory = $this->config->authnFactory;
        $this->authnFactory = new $authn_factory($this->services);

        // Create models.
        $conn = $this->services->db()->getConnection();
        $this->userModel = new UserModel($conn);
        $this->userAuthnMethodModel = new UserAuthnMethodModel($conn);
    }

    /**
     * Try to set the current user from the given login and password.
     */
    public function accredit(string $login, string $pass): AccreditationResult
    {
        // Check if a session is active.
        if ($this->session === null) {
            return AccreditationResult::NO_ACTIVE_SESSION;
        }

        // Get user.
        $user = $this->userModel->retrieveWithLogin($login);
        if ($user === null) {
            return AccreditationResult::WRONG_LOGIN;
        }

        // Check password.
        if (!$user->testPassword($pass)) {
            return AccreditationResult::WRONG_PASSWORD;
        }

        // Save user.
        $this->user = $user;

        // Save accreditation status.
        $this->session->set('user', []);
        $this->session->set('user.id', $this->user->id);
        $this->session->commit(false);

        return AccreditationResult::SUCCESS;
    }

    /**
     * Attempt to complete the active authentication process.
     */
    public function authenticate(array $data): AuthnResult
    {
        // Check if a session is active.
        if ($this->session === null) {
            return AuthnResult::NO_ACTIVE_SESSION;
        }

        // Check if an user is active.
        if ($this->user === null) {
            return AuthnResult::NO_ACCREDITED_USER;
        }

        // Get the user ID.
        $user_id = $this->user->id;

        // Get the authentication method ID.
        $id = $this->session->get('user.authn.current');
        if ($id === null) {
            return AuthnResult::NOT_REQUESTED;
        }

        // Get authentication method.
        $authn = $this->userAuthnMethodModel->retrieveForUser($user_id, $id);
        if ($authn === null) {
            return AuthnResult::NOT_FOUND;
        }

        // Check if the authentication method exists.
        if (!method_exists($this->authnFactory, $authn->name)) {
            return AuthnResult::INVALID_METHOD;
        }

        // Validate data.
        $instance = $this->getAuthentication($authn);
        $is_valid = $instance->validate($data);

        // Save validation success.
        if ($is_valid) {
            $this->session->set('user.authn.current', null);
            $this->session->set("user.authn.completed.{$authn->id}", time());
            $this->session->commit(false);
        }

        return $is_valid ? AuthnResult::SUCCESS : AuthnResult::FAILURE;
    }

    /**
     * Get available authentication options for the current user.
     * 
     * @return array<AuthnOption>
     */
    public function getAuthnOptions(): array
    {
        // Check if we have a user.
        if ($this->getStatus() !== UserStatus::AWAITING_AUTHENTICATION) {
            $message = 'Unexpected authentication options request.';
            throw new \RuntimeException($message);
        }

        // Get authentication methods.
        $authn_methods = $this->userAuthnMethodModel
            ->withColumns('id', 'name')
            ->listForUser($this->user->id);
        
        // Create options.
        $options = [];
        foreach ($authn_methods as $authn_method) {
            $option = new AuthnOption();
            $option->id = $authn_method->id;
            $option->name = $authn_method->name;
            $options[] = $option;
        }

        return $options;
    }

    /**
     * Get the current authorization status.
     */
    public function getStatus(): UserStatus
    {
        // Check if a section is active.
        if ($this->session === null) {
            return UserStatus::NO_ACTIVE_SESSION;
        }

        // Check if an user is logged in.
        if ($this->user === null) {
            return UserStatus::NO_ACCREDITED_USER;
        }

        // Check if MFA is active for this user.
        if ($this->user->authentication_steps < 1) {
            return UserStatus::ACCREDITED;
        }

        // Check completed processes.
        $completed = $this->session->get("user.authn.completed");
        $count = is_array($completed) ? count($completed) : 0;
        if ($count >= $this->user->authentication_steps) {
            return UserStatus::AUTHENTICATED;
        }

        return UserStatus::AWAITING_AUTHENTICATION;
    }

    /**
     * Remove all user data from this session.
     */
    public function logout(bool $destroy_session = false): void
    {
        // Remove the user instance.
        $this->user = null;

        // Destroy the session or empty its user data.
        if ($destroy_session) {
            $this->session->destroy();
            $this->session = null;
        } else {
            $this->session->set('user', []);
            $this->session->commit(false);
        }
    }

    /**
     * Start a new authentication process for the current user.
     */
    public function requestAuthn(string $id): AuthnRequestResult
    {
        // Check if a session is active.
        if ($this->session === null) {
            return AuthnRequestResult::NO_ACTIVE_SESSION;
        }

        // Check if a session is active.
        if ($this->user === null) {
            return AuthnRequestResult::NO_ACCREDITED_USER;
        }

        // Check if is trying to repeat a completed procedure.
        $completed = $this->session->get('user.authn.completed');
        if (is_array($completed) && array_key_exists($id, $completed)) {
            return AuthnRequestResult::ALREADY_COMPLETED;
        }

        // Get the user registered authentication method.
        $user_id = $this->user->id;
        $authn = $this->userAuthnMethodModel->retrieveForUser($user_id, $id);
        if ($authn === null) {
            return AuthnRequestResult::NOT_FOUND;
        }

        // Check if the authentication method exists.
        if (!method_exists($this->authnFactory, $authn->name)) {
            return AuthnRequestResult::INVALID_METHOD;
        }

        // Save data to session.
        $this->session->set('user.authn.current', $authn->id);
        $this->session->commit(false);

        // Get authentication object and request process.
        $instance = $this->getAuthentication($authn);
        $instance->request();

        return AuthnRequestResult::REQUESTED;
    }

    /**
     * Set the active session from a session ID.
     */
    public function setSession(null|string $session_id): static
    {
        // Close any active session.
        if ($this->session !== null) {
            $this->session->close();
            $this->session = null;
            $this->user = null;
        }

        // Stop if no session ID was passed.
        if ($session_id === null) {
            return $this;
        }

        // Open the session with the given ID.
        $this->session = $this->services->session()->getSession($session_id);
        $this->session->open();

        // Check if the user is active.
        $user_id = $this->session->get('user.id');
        if ($user_id) {
            $this->user = $this->userModel->retrieve($user_id);
        }

        return $this;
    }

    /**
     * Get an `AuthnInterface` object from an authentication method record.
     */
    protected function getAuthentication(UserAuthnMethod $record): AuthnInterface
    {
        // Decode settings.
        $settings = json_decode($record->settings, true);

        // Get and configure an instance from the factory.
        $authn = $this->authnFactory->{$record->name}();
        $authn->configure($settings);

        return $authn;
    }
}
