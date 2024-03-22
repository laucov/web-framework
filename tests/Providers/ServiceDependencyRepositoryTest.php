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

namespace Tests\Providers;

use Laucov\WebFramework\Config\Interfaces\ConfigInterface;
use Laucov\WebFramework\Providers\ConfigProvider;
use Laucov\WebFramework\Providers\ServiceDependencyRepository;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Providers\ServiceDependencyRepository
 */
class ServiceDependencyRepositoryTest extends TestCase
{
    /**
     * @covers ::getValue
     * @covers ::setConfigProvider
     * @uses Laucov\WebFramework\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFramework\Providers\ConfigProvider::addConfig
     * @uses Laucov\WebFramework\Providers\ConfigProvider::createInstance
     * @uses Laucov\WebFramework\Providers\ConfigProvider::getConfig
     * @uses Laucov\WebFramework\Providers\ConfigProvider::getInstance
     * @uses Laucov\WebFramework\Providers\ConfigProvider::getName
     * @uses Laucov\WebFramework\Providers\ConfigProvider::hasConfig
     */
    public function testCanResolveConfigurationDependencies(): void
    {
        // Set configuration.
        $config = new ConfigProvider([]);
        $config->addConfig(ConfigA::class);

        // Create repository.
        $repo = new ServiceDependencyRepository();
        $repo->setConfigProvider($config);

        // Assert that can get from provider.
        $this->assertInstanceOf(
            ConfigA::class,
            $repo->getValue(ConfigA::class),
        );

        // @todo Test fallback.
    }
}

class ConfigA implements ConfigInterface
{}

class ConfigB implements ConfigInterface
{}

class ConfigC implements ConfigInterface
{}
