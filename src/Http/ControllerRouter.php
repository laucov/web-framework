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

namespace Laucov\WebFwk\Http;

use Laucov\Http\Routing\Router;
use Laucov\WebFwk\Providers\ConfigProvider;
use Laucov\WebFwk\Providers\ServiceProvider;

/**
 * Routes controller requests.
 */
class ControllerRouter extends Router
{
    /**
     * Current configuration provider in use.
     */
    protected ConfigProvider $configProvider;

    /**
     * Current controller class name.
     * 
     * @var class-string<AbstractController>
     */
    protected string $controllerName;

    /**
     * Active CRUD operations.
     * 
     * @var array<Crud>
     */
    protected array $crudOps = [
        Crud::CREATE,
        Crud::READ,
        Crud::READ_ALL,
        Crud::UPDATE,
        Crud::DELETE,
    ];

    /**
     * Current CRUD path options.
     * 
     * @var array<Crud, array{string, bool}>
     */
    protected array $crudPaths = [
        'CREATE' => ['POST', 'create', false],
        'READ' => ['GET', 'retrieve', true],
        'READ_ALL' => ['GET', 'list', false],
        'UPDATE' => ['PATCH', 'update', true],
        'DELETE' => ['DELETE', 'delete', true],
    ];

    /**
     * Whether to reset the CRUD operations list after the next CRUD routing.
     */
    protected bool $resetCrud = true;

    /**
     * Current service provider in use.
     */
    protected ServiceProvider $serviceProvider;

    /**
     * Exclude CRUD operations from the list in use.
     */
    public function resetCrudOps(): static
    {
        $this->crudOps = [
            Crud::CREATE,
            Crud::READ,
            Crud::READ_ALL,
            Crud::UPDATE,
            Crud::DELETE,
        ];

        return $this;
    }

    /**
     * Set the configuration of a CRUD path option.
     */
    public function setCrudPath(
        Crud $operation,
        string $http_method,
        string $controller_method,
        bool $has_id,
    ): static {
        // Set CRUD configuration.
        $data = [$http_method, $controller_method, $has_id];
        $this->crudPaths[$operation->name] = $data;

        return $this;
    }

    /**
     * Route CRUD methods for the current controller.
     */
    public function setCrudRoutes(string $path_pref, string $id_patt): static
    {
        foreach ($this->crudOps as $operation) {
            [$method, $func_name, $has_id] = $this->crudPaths[$operation->name];
            $path = $has_id ? (rtrim($path_pref) . "/{$id_patt}") : $path_pref;
            $this->setMethodRoute($method, $path, $func_name);
        }

        if ($this->resetCrud) {
            $this->resetCrudOps();
        }

        return $this;
    }

    /**
     * Route one of the current controller methods.
     */
    public function setMethodRoute(
        string $http_method,
        string $path,
        string $controller_method,
    ): static {
        $this->setClassRoute(
            $http_method,
            $path,
            $this->controllerName,
            $controller_method,
            $this->configProvider,
            $this->serviceProvider,
        );

        return $this;
    }

    /**
     * Set which CRUD operations should be routed from now on.
     */
    public function setCrudOps(Crud ...$operations): static
    {
        $this->crudOps = $operations;
        $this->resetCrud = false;
        return $this;
    }

    /**
     * Set the current controller in use.
     */
    public function setController(string $class_name): static
    {
        if (!class_exists($class_name)) {
            $message = $class_name . ' does not exist.';
            throw new \InvalidArgumentException($message);
        }

        if (!is_a($class_name, AbstractController::class, true)) {
            $message = 'All controller classes must extend %s.';
            $message = sprintf($message, AbstractController::class);
            throw new \InvalidArgumentException($message);
        }

        $this->controllerName = $class_name;

        return $this;
    }

    /**
     * Set the current providers in use.
     */
    public function setProviders(
        ConfigProvider $config,
        ServiceProvider $services,
    ): static {
        $this->configProvider = $config;
        $this->dependencies->setValue(ConfigProvider::class, $config);
        $this->serviceProvider = $services;
        $this->dependencies->setValue(ServiceProvider::class, $services);

        return $this;
    }

    /**
     * Set which CRUD operations should be routed from now on.
     */
    public function withCrudOps(Crud ...$operations): static
    {
        $this->crudOps = $operations;
        $this->resetCrud = true;
        return $this;
    }
}
