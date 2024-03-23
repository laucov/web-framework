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

namespace Laucov\WebFwk\Providers;

use Laucov\Injection\Repository;
use Laucov\WebFwk\Config\Interfaces\ConfigInterface;

/**
 * Stores service dependency sources.
 */
class ServiceDependencyRepository extends Repository
{
    /**
     * Configuration provider.
     */
    protected ConfigProvider $configProvider;

    /**
     * Get a dependency value.
     * 
     * Check the provider first for `ConfigInterface` dependencies.
     */
    public function getValue(string $name): mixed
    {
        if (
            isset($this->configProvider)
            && is_a($name, ConfigInterface::class, true)
            && $this->configProvider->hasConfig($name)
        ) {
            return $this->configProvider->getConfig($name);
        }

        return parent::getValue($name);
    }

    /**
     * Check whether a dependency type is registered.
     * 
     * Check the provider first for `ConfigInterface` dependencies.
     */
    public function hasDependency(string $name): bool
    {
        if (
            isset($this->configProvider)
            && is_a($name, ConfigInterface::class, true)
            && $this->configProvider->hasConfig($name)
        ) {
            return true;
        }

        return parent::hasDependency($name);
    }

    /**
     * Set the configuration provider used to satisfy configuration dependencies.
     */
    public function setConfigProvider(ConfigProvider $provider): static
    {
        $this->configProvider = $provider;
        return $this;
    }
}
