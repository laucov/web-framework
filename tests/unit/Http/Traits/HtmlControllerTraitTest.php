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

namespace Tests\Unit\Http\Traits;

use Laucov\Http\Message\OutgoingResponse;
use Laucov\Views\View;
use Laucov\WebFwk\Config\Display;
use Laucov\WebFwk\Http\Traits\HtmlControllerTrait;
use Laucov\WebFwk\Providers\ConfigProvider;
use Laucov\WebFwk\Providers\ServiceProvider;
use Laucov\WebFwk\Services\LanguageService;
use Laucov\WebFwk\Services\ViewService;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Http\Traits\HtmlControllerTrait
 */
class HtmlControllerTraitTest extends TestCase
{
    /**
     * @covers ::setView
     */
    public function testCanSetViewsAsResponses(): void
    {
        // Mock dependencies.
        $display_config = $this->createMock(Display::class);
        $config = $this->createMock(ConfigProvider::class);
        $lang_service = $this->createMock(LanguageService::class);
        $response = $this->createMock(OutgoingResponse::class);
        $services = $this->createMock(ServiceProvider::class);
        $view = $this->createMock(View::class);
        $view_service = $this->createMock(ViewService::class);

        // Create controller.
        $controller = new class {
            use HtmlControllerTrait;
            public ConfigProvider $config;
            public OutgoingResponse $response;
            public ServiceProvider $services;
            public function test(): void
            {
                // Set a basic view.
                $this->setView('/path/to/view', [
                    'search' => 'What is a foobar?',
                    'page' => 2,
                ]);
            }
        };

        // Set properties.
        $controller->config = $config;
        $controller->response = $response;
        $controller->services = $services;

        // Set mock properties.
        $display_config->author = 'John Doe';
        $display_config->colorMode = 'dark';
        $display_config->description = 'A very nice website.';
        $display_config->faviconPath = '/media/favicon.png';
        $display_config->faviconType = 'image/png';
        $display_config->title = 'Foobarinator 3000';

        // Set expectations.
        $map = [[Display::class, $display_config]];
        $config
            ->method('getConfig')
            ->will($this->returnValueMap($map));
        $services
            ->method('lang')
            ->willReturn($lang_service);
        $services
            ->method('view')
            ->willReturn($view_service);
        $map = [['/path/to/view', $view]];
        $view_service
            ->method('getView')
            ->will($this->returnValueMap($map));
        $view
            ->expects($this->once())
            ->method('get')
            ->with([
                'app_author' => 'John Doe',
                'app_color_mode' => 'dark',
                'app_description' => 'A very nice website.',
                'app_favicon_path' => '/media/favicon.png',
                'app_favicon_type' => 'image/png',
                'app_title' => 'Foobarinator 3000',
                'lang' => $lang_service,
                'page' => 2,
                'search' => 'What is a foobar?',
            ])
            ->willReturn('Some HTML...');
        $response
            ->expects($this->once())
            ->method('setStatus')
            ->with(200, 'OK')
            ->willReturn($response);
        $response
            ->expects($this->once())
            ->method('setHeaderLine')
            ->with('Content-Type', 'text/html')
            ->willReturn($response);
        $response
            ->expects($this->once())
            ->method('setBody')
            ->with('Some HTML...');

        // Test.
        $controller->test();
    }
}
