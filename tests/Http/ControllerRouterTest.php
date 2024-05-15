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

use Laucov\Http\Message\IncomingRequest;
use Laucov\Http\Routing\Call\Interfaces\PreludeInterface;
use Laucov\Http\Routing\Router;
use Laucov\WebFwk\Config\Interfaces\ConfigInterface;
use Laucov\WebFwk\Http\AbstractController;
use Laucov\WebFwk\Http\ControllerRouter;
use Laucov\WebFwk\Http\Crud;
use Laucov\WebFwk\Providers\ConfigProvider;
use Laucov\WebFwk\Providers\ServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Http\ControllerRouter
 */
class ControllerRouterTest extends TestCase
{
    /**
     * @covers ::resetCrudOps
     * @covers ::setController
     * @covers ::setCrudOps
     * @covers ::setCrudPath
     * @covers ::setCrudRoutes
     * @covers ::setMethodRoute
     * @covers ::setProviders
     * @covers ::withCrudOps
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::setConfigProvider
     * @uses Laucov\WebFwk\Providers\ServiceProvider::__construct
     */
    public function testCanRouteControllers(): void
    {
        // Create providers.
        $c = new ConfigProvider([]);
        $s = new ServiceProvider($c);

        // Mock router.
        $router = $this->getMockBuilder(ControllerRouter::class)
            ->onlyMethods(['setClassRoute'])
            ->getMock();
        $router
            ->expects($this->exactly(18))
            ->method('setClassRoute')
            ->withConsecutive(
                ['GET', '/flights', A::class, 'list', $c, $s],
                ['POST', 'names', B::class, 'create', $c, $s],
                ['GET', 'names/:int', B::class, 'retrieve', $c, $s],
                ['GET', 'names', B::class, 'list', $c, $s],
                ['PATCH', 'names/:int', B::class, 'update', $c, $s],
                ['DELETE', 'names/:int', B::class, 'delete', $c, $s],
                ['GET', 'cars/:int', C::class, 'retrieve', $c, $s],
                ['GET', 'cars', C::class, 'list', $c, $s],
                ['PUT', 'cars', C::class, 'replace', $c, $s],
                ['GET', 'animals', D::class, 'list', $c, $s],
                ['POST', 'animals', D::class, 'create', $c, $s],
                ['GET', 'foobars', E::class, 'list', $c, $s],
                ['POST', 'foobars', E::class, 'create', $c, $s],
                ['POST', 'fruits', F::class, 'create', $c, $s],
                ['GET', 'fruits/:int', F::class, 'retrieve', $c, $s],
                ['GET', 'fruits', F::class, 'list', $c, $s],
                ['PUT', 'fruits', F::class, 'replace', $c, $s],
                ['DELETE', 'fruits/:int', F::class, 'delete', $c, $s],
            );

        // Set routes.
        $this->assertInstanceOf(Router::class, $router);
        $router
            ->setProviders($c, $s)
            ->setController(A::class)
                ->setMethodRoute('GET', '/flights', 'list')
            ->setController(B::class)
                ->setCrudRoutes('names', ':int')
            ->setCrudPath(Crud::UPDATE, 'PUT', 'replace', false)
            ->withCrudOps(Crud::READ, Crud::READ_ALL, Crud::UPDATE)
            ->setController(C::class)
                ->setCrudRoutes('cars', ':int')
            ->setCrudOps(Crud::READ_ALL, Crud::CREATE)
            ->setController(D::class)
                ->setCrudRoutes('animals', ':int')
            ->setController(E::class)
                ->setCrudRoutes('foobars', ':int')
            ->resetCrudOps()
            ->setController(F::class)
                ->setCrudRoutes('fruits', ':int');
    }

    /**
     * @covers ::setController
     * @uses Laucov\WebFwk\Http\ControllerRouter::setProviders
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::setConfigProvider
     * @uses Laucov\WebFwk\Providers\ServiceProvider::__construct
     */
    public function testControllerClassMustExist(): void
    {
        // Create router.
        $config = new ConfigProvider([]);
        $router = new ControllerRouter();
        $router->setProviders($config, new ServiceProvider($config));

        // Attempt to set an invalid controller.
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Foo\Bar\Baz does not exist.');
        $router->setController('Foo\Bar\Baz');
    }

    /**
     * @covers ::setController
     * @uses Laucov\WebFwk\Http\ControllerRouter::setProviders
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::setConfigProvider
     * @uses Laucov\WebFwk\Providers\ServiceProvider::__construct
     */
    public function testControllerMustExtendAbstractController(): void
    {
        // Create router.
        $config = new ConfigProvider([]);
        $router = new ControllerRouter();
        $router->setProviders($config, new ServiceProvider($config));

        // Attempt to set an invalid controller.
        $this->expectException(\InvalidArgumentException::class);
        $abstract_ctrl = AbstractController::class;
        $message = 'All controller classes must extend ' . $abstract_ctrl;
        $this->expectExceptionMessage($message);
        $router->setController(G::class);
    }

    /**
     * @covers ::setProviders
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ConfigProvider::addConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::createInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getName
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::setConfigProvider
     * @uses Laucov\WebFwk\Providers\ServiceProvider::__construct
     */
    public function testProvidersCanBeUsedAsDependencies(): void
    {
        // Create router.
        $config = new ConfigProvider([]);
        $config->addConfig(ExampleConfig::class);
        $router = new ControllerRouter();
        $router->setProviders($config, new ServiceProvider($config));

        // Create function.
        $callable = function (ServiceProvider $s, ConfigProvider $c): string {
            $config = $c->getConfig(ExampleConfig::class);
            $config->paramA = 'bar';
            $message = 'Parameters are "%s" and "%s".';
            return sprintf($message, $config->paramA, $config->paramB);
        };

        // Set route and prelude.
        $router
            ->addPrelude('example', ExamplePrelude::class, [])
            ->setPreludes('example')
            ->setCallableRoute('GET', '/', $callable);

        // Test output.
        $request = new IncomingRequest('');
        $result = (string) $router->findRoute($request)->run()->getBody();
        $this->assertSame('Parameters are "bar" and "baz".', $result);
    }
}

class ExampleConfig implements ConfigInterface
{
    public string $paramA = 'foo';
    public string $paramB = 'bar';
}

class ExamplePrelude implements PreludeInterface
{
    public function __construct(
        protected ConfigProvider $config,
        protected ServiceProvider $services,
    ) {
    }

    public function run(): null
    {
        $this->config->getConfig(ExampleConfig::class)->paramB = 'baz';
        return null;
    }
}

class A extends AbstractController
{
}

class B extends AbstractController
{
}

class C extends AbstractController
{
}

class D extends AbstractController
{
}

class E extends AbstractController
{
}

class F extends AbstractController
{
}

class G
{
}
