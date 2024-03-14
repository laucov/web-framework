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

namespace Tests\Services;

use Laucov\WebFramework\Config\Language;
use Laucov\WebFramework\Services\Interfaces\ServiceInterface;
use Laucov\WebFramework\Services\LanguageService;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Services\LanguageService
 */
class LanguageServiceTest extends TestCase
{
    /**
     * Configuration instance.
     */
    protected Language $config;

    /**
     * Service instance.
     */
    protected LanguageService $service;

    /**
     * @covers ::__construct
     * @covers ::findMessage
     * @covers ::getLocale
     * @covers ::setLocale
     * @covers ::update
     */
    public function testCanSetAcceptedLanguagesAndFormatMessages(): void
    {
        $this->assertInstanceOf(ServiceInterface::class, $this->service);

        // Test with no accepted locale.
        $this->assertSame('en', $this->service->getLocale());

        // Test with accepted and supported locale.
        $locale = $this->service
            ->setLocale('es-MX', 'pt-BR', 'en')
            ->getLocale();
        $this->assertSame('pt-BR', $locale);

        // Test with redirected locale.
        $this->service->setLocale('es-MX', 'pt', 'en');
        $this->assertSame('pt-BR', $this->service->getLocale());

        // Test getting messages.
        $msg_a = $this->service->findMessage('greetings.hello', ['John']);
        $this->assertSame('Olá, John!', $msg_a);
        $this->service->setLocale('en');
        $msg_b = $this->service->findMessage('greetings.hello', ['John']);
        $this->assertSame('Hello, John!', $msg_b);

        // Test fallback.
        $this->service->setLocale('pt-BR');
        $msg_c = $this->service->findMessage('greetings.hey', ['John']);
        $this->assertSame('Hey, John!', $msg_c);
    }

    protected function setUp(): void
    {
        // Create configuration.
        $this->config = new class () extends Language {};
        $this->config->defaultLocale = 'en';
        $this->config->redirects['pt'] = 'pt-BR';
        $this->config->supportedLocales[] = 'pt-BR';
        $this->config->supportedLocales[] = 'pt';
        $this->config->directories[] = __DIR__ . '/lang-files';
        $this->config->data['en'] = [
            'greetings' => [
                'hi' => 'Hi, {0}!',
                'hello' => 'Hello, {0}!',
                'hey' => 'Hey, {0}!',
            ],
        ];

        // Instantiate.
        $this->service = new LanguageService($this->config);
    }
}
