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

use Laucov\WebFramework\Config\View;
use Laucov\WebFramework\Services\Interfaces\ServiceInterface;
use Laucov\WebFramework\Services\ViewService;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Services\ViewService
 */
class ViewServiceTest extends TestCase
{
    /**
     * Configuration instance.
     */
    protected View $config;

    /**
     * Service instance.
     */
    protected ViewService $service;

    /**
     * @covers ::__construct
     * @covers ::getView
     */
    public function testCanGetViews(): void
    {
        $this->assertInstanceOf(ServiceInterface::class, $this->service);
        $view = $this->service->getView('view-a');
        $this->assertInstanceOf(\Laucov\Views\View::class, $view);
        $this->assertSame('<p>Hello, World!</p>', $view->get());
    }

    protected function setUp(): void
    {
        // Create configuration.
        $this->config = new class () extends View {};
        $this->config->viewsDir = __DIR__ . '/view-files';
        $this->config->cacheDir = __DIR__ . '/view-cache';

        // Instantiate.
        $this->service = new ViewService($this->config);
    }
}
