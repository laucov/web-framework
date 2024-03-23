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
use Laucov\WebFwk\Providers\EnvMatch;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Providers\ConfigProvider
 */
class ConfigProviderTest extends TestCase
{
    protected ConfigProvider $provider;

    /**
     * @covers ::__construct
     * @covers ::addConfig
     * @covers ::createInstance
     * @covers ::getInstance
     * @covers ::getConfig
     * @covers ::getName
     * @covers ::hasConfig
     * @uses Laucov\WebFwk\Providers\EnvMatch::__construct
     */
    public function testCanAddAndGetConfigs(): void
    {
        // Test setting and getting configuration objects.
        $this->provider->addConfig(Book::class);
        $config_a = $this->provider->getConfig(Book::class);
        $config_b = $this->provider->getConfig(Book::class);
        $this->assertSame($config_a, $config_b);
        $this->assertSame('Example title', $config_a->title);
        $this->assertSame('Doe, John', $config_a->author);
        $this->assertSame(2024, $config_a->year);

        // Test with environment.
        $provider = new ConfigProvider([
            'BOOK_AUTHOR' => 'Johnson, Carl',
            'BOOK_YEAR' => '1997',
        ]);
        $provider->addConfig(Book::class);
        $config_c = $provider->getConfig(Book::class);
        $this->assertSame('Example title', $config_c->title);
        $this->assertSame('Johnson, Carl', $config_c->author);
        $this->assertSame(1997, $config_c->year);

        // Test if can extend default configuration objects.
        $provider = new ConfigProvider([]);
        $provider->addConfig(\Tests\Providers\Another\Book::class);
        $config_d = $provider->getConfig(\Tests\Providers\Another\Book::class);
        $this->assertInstanceOf(\Tests\Providers\Another\Book::class, $config_d);
        $config_e = $provider->getConfig(Book::class);
        $this->assertSame($config_d, $config_e);

        // Check if has config.
        $this->assertTrue($this->provider->hasConfig(Book::class));
        $this->assertTrue(
            $this->provider->hasConfig(\Tests\Providers\Another\Book::class),
        );
    }

    /**
     * @covers ::addConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getName
     */
    public function testCannotAddTheSameConfigTwice(): void
    {
        $this->provider->addConfig(Book::class);
        $this->expectException(\RuntimeException::class);
        $this->provider->addConfig(\Tests\Providers\Another\Book::class);
    }

    /**
     * @covers ::getConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getName
     */
    public function testMustSetBeforeGetting(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->provider->getConfig(Book::class);
    }

    /**
     * @covers ::addConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     */
    public function testMustUseConfigurationClassesToSetConfigs(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->provider->addConfig(\stdClass::class);
    }

    /**
     * @covers ::getConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ConfigProvider::addConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::createInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getName
     * @uses Laucov\WebFwk\Providers\EnvMatch::__construct
     */
    public function testRegisteredConfigMustExtendTheRequestedOne(): void
    {
        $this->provider->addConfig(Book::class);
        $this->expectException(\RuntimeException::class);
        $this->provider->getConfig(\Tests\Providers\Another\Book::class);
    }

    protected function setUp(): void
    {
        $this->provider = new ConfigProvider([]);
    }
}

#[EnvMatch('BOOK_AUTHOR', 'author')]
#[EnvMatch('BOOK_YEAR', 'year')]
class Book implements ConfigInterface
{
    public string $title = 'Example title';
    public string $author = 'Doe, John';
    public int $year = 2024;
}

namespace Tests\Providers\Another;

class Book extends \Tests\Providers\Book
{
}
