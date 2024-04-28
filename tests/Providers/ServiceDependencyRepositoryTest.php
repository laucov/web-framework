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

use Laucov\WebFwk\Config\Interfaces\ConfigInterface;
use Laucov\WebFwk\Providers\ConfigProvider;
use Laucov\WebFwk\Providers\ServiceDependencyRepository;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Providers\ServiceDependencyRepository
 */
class ServiceDependencyRepositoryTest extends TestCase
{
    /**
     * @covers ::getValue
     * @covers ::hasDependency
     * @covers ::setConfigProvider
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ConfigProvider::addConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::createInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getName
     * @uses Laucov\WebFwk\Providers\ConfigProvider::hasConfig
     */
    public function testCanResolveConfigurationDependencies(): void
    {
        // Create repository.
        $repo = new ServiceDependencyRepository();

        // Assert that can use without configuration.
        $repo->setValue(ConfigA::class, new ConfigA());
        $this->assertTrue($repo->hasDependency(ConfigA::class));
        $this->assertFalse($repo->hasDependency(ConfigB::class));
        $this->assertFalse($repo->hasDependency(ConfigC::class));
        $this->assertInstanceOf(
            ConfigA::class,
            $repo->getValue(ConfigA::class),
        );

        // Set configuration.
        $config = new ConfigProvider([]);
        $config->addConfig(ConfigB::class);
        $repo->setConfigProvider($config);

        // Assert that can get from provider.
        $this->assertTrue($repo->hasDependency(ConfigB::class));
        $this->assertFalse($repo->hasDependency(ConfigC::class));
        $this->assertInstanceOf(
            ConfigB::class,
            $repo->getValue(ConfigB::class),
        );

        // Test fallback.
        $config_c = new ConfigC();
        $repo->setValue(ConfigC::class, $config_c);
        $this->assertTrue($repo->hasDependency(ConfigC::class));
        $this->assertInstanceOf(
            ConfigC::class,
            $repo->getValue(ConfigC::class),
        );

        // Assert that prefers manual dependencies.
        $config->addConfig(ConfigC::class);
        $result = $repo->getValue(ConfigC::class);
        $this->assertTrue($repo->hasDependency(ConfigC::class));
        $this->assertInstanceOf(ConfigC::class, $result);
        $this->assertNotSame($config_c, $result);
    }
}

class ConfigA implements ConfigInterface
{
}

class ConfigB implements ConfigInterface
{
}

class ConfigC implements ConfigInterface
{
}
