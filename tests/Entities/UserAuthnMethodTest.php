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

namespace Tests\Entities;

use Laucov\WebFwk\Entities\UserAuthnMethod;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Entities\UserAuthnMethod
 */
class UserAuthnMethodTest extends TestCase
{
    protected UserAuthnMethod $entity;

    /**
     * @covers ::getSettings
     * @covers ::setSettings
     */
    public function testSetsAndGetsSettings(): void
    {
        // Set initial JSON.
        $this->entity->settings = '{"address":"john@doe.com","secret":"Potatoes!"}';

        // Get settings.
        $data = $this->entity->getSettings();
        $this->assertIsArray($data);
        $this->assertSame('john@doe.com', $data['address'] ?? null);
        $this->assertSame('Potatoes!', $data['secret'] ?? null);
        $this->assertCount(2, $data);

        // Set new settings.
        $data = ['address' => 'john@gov.br', 'secret' => 'Hey.'];
        $this->entity->setSettings($data);
        $expected = '{"address":"john@gov.br","secret":"Hey."}';
        $this->assertSame($expected, $this->entity->settings);
        $data = $this->entity->getSettings();
        $this->assertIsArray($data);
        $this->assertSame('john@gov.br', $data['address'] ?? null);
        $this->assertSame('Hey.', $data['secret'] ?? null);
        $this->assertCount(2, $data);

        // Ensure that all JSON containers are objects ("{}").
        $this->entity->setSettings([]);
        $this->assertSame('{}', $this->entity->settings);
        $this->entity->setSettings(['abcd', ['hey', 'ho'], ['lets', 'go']]);
        $expected = '{"0":"abcd","1":["hey","ho"],"2":["lets","go"]}';
        $this->assertSame($expected, $this->entity->settings);
        $this->entity->settings = '[]';
        $this->entity->setSettings($this->entity->getSettings());
        $this->assertSame('{}', $this->entity->settings);
    }

    /**
     * @covers ::setSettings
     */
    public function testValidatesJsonStringsBeforeSetting(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = ['foo' => fopen('data://text/plain,hello', 'r')];
        $this->entity->setSettings($data);
    }

    /**
     * @covers ::getSettings
     */
    public function testValidatesJsonStringsAfterGetting(): void
    {
        $this->entity->settings = '{not a json lol}';
        $this->expectException(\RuntimeException::class);
        $this->entity->getSettings();
    }

    protected function setUp(): void
    {
        $this->entity = new UserAuthnMethod();
    }
}
