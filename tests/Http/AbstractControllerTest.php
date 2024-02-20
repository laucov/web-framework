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

namespace Tests\Http;

use Laucov\Db\Data\Driver\DriverFactory;
use Laucov\Http\Message\OutgoingRequest;
use Laucov\Http\Message\RequestInterface;
use Laucov\WebFramework\Database\ConnectionProvider;
use Laucov\WebFramework\Http\AbstractController;
use Laucov\WebFramework\Modeling\AbstractModel;
use Laucov\WebFramework\Modeling\AbstractEntity;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Http\AbstractController
 */
class AbstractControllerTest extends TestCase
{
    /**
     * @covers ::__construct
     * @uses Laucov\WebFramework\Database\ConnectionProvider::__construct
     */
    public function testCanExtend(): void
    {
        $this->expectNotToPerformAssertions();

        // Create request.
        $request = new OutgoingRequest();

        // Create connection provider.
        $provider = new ConnectionProvider(new DriverFactory());

        // Extend.
        new class ($request, $provider) extends AbstractController
        {
        };
    }
}
