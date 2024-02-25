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

use Laucov\WebFramework\Providers\AbstractService;
use Laucov\WebFramework\Providers\ConfigInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Providers\AbstractService
 */
class AbstractServiceTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testCanInstantiate(): void
    {
        // Create configuration.
        $config = new class implements ConfigInterface {};

        // Create instance.
        $instance = new class ($config) extends AbstractService
        {
            public function getConfig()
            {
                return $this->config;
            }
        };

        // Test instance.
        $this->assertSame($config, $instance->getConfig());
    }
}
