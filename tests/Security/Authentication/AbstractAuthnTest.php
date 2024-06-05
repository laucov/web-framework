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

use Laucov\Modeling\Entity\AbstractEntity;
use Laucov\WebFwk\Security\Authentication\AbstractAuthn;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Security\Authentication\AbstractAuthn
 */
class AbstractAuthnTest extends TestCase
{
    /**
     * @covers ::createSettings
     * @covers ::createSettingsFromArray
     * @covers ::configure
     */
    public function testCanConfigure(): void
    {
        // Create config class.
        $config = new class () extends AbstractEntity {
            public string $secret;
        };

        // Create instance.
        $authn = new class ($config::class) extends AbstractAuthn {
            public bool $didSetup = false;
            public function __construct(string $class_name)
            {
                $this->settingsEntity = $class_name;
            }
            public function getFields(): array
            {
                return [];
            }
            public function request(): void
            {
            }
            public function setup(): void
            {
                $this->didSetup = true;
            }
            public function validate(array $data): bool
            {
                return $data['guess'] === $this->settings->{'secret'};
            }
        };

        // Configure.
        $config->secret = 'foobar';
        $authn->configure($config);

        // Check `setup` was called.
        $this->assertTrue($authn->didSetup);

        // Validate - check if settings were stored.
        $this->assertFalse($authn->validate(['guess' => 'baz']));
        $this->assertTrue($authn->validate(['guess' => 'foobar']));

        // Create configuration from the authentication class.
        $config = $authn->createSettings();
        $config->secret = 'baz';
        $authn->configure($config);
        $this->assertFalse($authn->validate(['guess' => 'foobar']));
        $this->assertTrue($authn->validate(['guess' => 'baz']));

        // Create configuration from the authentication class - use array data.
        $result = $authn->createSettingsFromArray(['secret' => '1234']);
        $authn->configure($result->entity);
        $this->assertFalse($authn->validate(['guess' => '4321']));
        $this->assertTrue($authn->validate(['guess' => '1234']));

        // Test if checks the configuration class name.
        $config = new class () extends AbstractEntity {
            public string $value;
        };
        $this->expectException(\InvalidArgumentException::class);
        $authn->configure($config);
    }
}
