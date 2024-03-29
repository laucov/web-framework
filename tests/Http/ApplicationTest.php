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

use Laucov\Http\Message\RequestInterface;
use Laucov\Http\Message\ResponseInterface;
use Laucov\WebFwk\Config\Interfaces\ConfigInterface;
use Laucov\WebFwk\Http\AbstractController;
use Laucov\WebFwk\Http\Application;
use Laucov\WebFwk\Providers\EnvMatch;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Http\Application
 */
class ApplicationTest extends TestCase
{
    public function superGlobalsProvider(): array
    {
        return [
            'build request with server data' => [
                // $_COOKIE
                [],
                // $_ENV
                [],
                // php://input
                '',
                // $_GET
                [],
                // getallheaders()
                [],
                // $_POST
                [],
                // $_SERVER
                [
                    'REQUEST_URI' => '/flights',
                    'SERVER_PROTOCOL' => 'HTTP/1.0',
                ],
                // Expected output.
                'HTTP/1.0 200 OK' . "\n" .
                'Content-Type: application/json' . "\n" .
                'Content-Length: 184' . "\n" .
                '{"data":[' .
                '{"id":1,"call_sign":"AZU4991","aircraft":"ATR 72-600"},' .
                '{"id":2,"call_sign":"GLO1212","aircraft":"Boeing 737-7BX"},' .
                '{"id":3,"call_sign":"TAM3279","aircraft":"Airbus A319-132"}' .
                ']}',
            ],
            'change protocol version and test cookies' => [
                // $_COOKIE
                [
                    'theme' => 'dark',
                ],
                // $_ENV
                [],
                // php://input
                '',
                // $_GET
                [],
                // getallheaders()
                [],
                // $_POST
                [],
                // $_SERVER
                [
                    'REQUEST_URI' => '/home',
                    'SERVER_PROTOCOL' => 'HTTP/1.1',
                ],
                // Expected output.
                "HTTP/1.1 200 OK\n" .
                "Content-Type: text/html\n" .
                "Content-Length: 104\n" .
                <<<HTML
                    <!DOCTYPE html>
                    <html>
                    <body class="body--dark">
                    <h1>My App</h1>
                    <p>Howdy, Stranger!</p>
                    </body>
                    </html>
                    HTML,
            ],
            'test headers and environment' => [
                // $_COOKIE
                [],
                // $_ENV
                [
                    'APP_NAME' => 'The App',
                ],
                // php://input
                '',
                // $_GET
                [],
                // getallheaders()
                [],
                // $_POST
                [],
                // $_SERVER
                [
                    'REQUEST_URI' => '/home',
                    'SERVER_PROTOCOL' => 'HTTP/1.0',
                ],
                // Expected output.
                "HTTP/1.0 200 OK\n" .
                "Content-Type: text/html\n" .
                "Content-Length: 106\n" .
                <<<HTML
                    <!DOCTYPE html>
                    <html>
                    <body class="body--light">
                    <h1>The App</h1>
                    <p>Hello, John!</p>
                    </body>
                    </html>
                    HTML,
            ],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::createRouter
     * @covers ::getRouter
     * @covers ::getStatusLine
     * @covers ::run
     * @covers ::setConfigClasses
     * @covers ::setCookies
     * @covers ::setEnvironment
     * @covers ::setHeaders
     * @covers ::setInputFilename
     * @covers ::setOutputCallables
     * @covers ::setParameters
     * @covers ::setPostVariables
     * @covers ::setServerInfo
     * @uses Laucov\WebFwk\Http\AbstractController::__construct
     * @uses Laucov\WebFwk\Http\ControllerRouter::setController
     * @uses Laucov\WebFwk\Http\ControllerRouter::setMethodRoute
     * @uses Laucov\WebFwk\Http\ControllerRouter::setProviders
     * @uses Laucov\WebFwk\Providers\ConfigProvider::__construct
     * @uses Laucov\WebFwk\Providers\ConfigProvider::addConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::createInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getConfig
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getInstance
     * @uses Laucov\WebFwk\Providers\ConfigProvider::getName
     * @uses Laucov\WebFwk\Providers\ConfigProvider::hasConfig
     * @uses Laucov\WebFwk\Providers\EnvMatch::__construct
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::getValue
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::hasDependency
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::setConfigProvider
     * @uses Laucov\WebFwk\Providers\ServiceProvider::__construct
     * @uses Laucov\WebFwk\Providers\ServiceProvider::getService
     * @uses Laucov\WebFwk\Providers\ServiceProvider::view
     * @uses Laucov\WebFwk\Services\ViewService::__construct
     * @uses Laucov\WebFwk\Services\ViewService::getView
     * @dataProvider superGlobalsProvider
     */
    public function testCanRunApplication(
        array $cookies = [],
        array $environment = [],
        string $input_filename = '',
        array $get = [],
        array $headers = [],
        array $post = [],
        array $server = [],
        string $expected,
    ): void {
        // Create application instance.
        $application = new Application();

        // Create callables.
        $print = function (string $data) {
            echo $data;
        };
        $println = function (string $data) {
            echo $data . "\n";
        };

        // Set variables.
        $application
            ->setConfigClasses(
                App::class,
                View::class,
            )
            ->setCookies($cookies)
            ->setEnvironment($environment)
            ->setHeaders($headers)
            ->setInputFilename($input_filename)
            ->setOutputCallables(
                $println,
                $println,
                $print,
            )
            ->setParameters($get)
            ->setPostVariables($post)
            ->setServerInfo($server);
        
        // Create routes.
        $application
            ->getRouter()
            ->setController(FlightController::class)
            ->setMethodRoute('GET', 'home', 'home')
            ->pushPrefix('flights')
                ->setMethodRoute('GET', '', 'list');
        
        // Run the request.
        $this->expectOutputString($expected);
        $application->run();
    }
}

// Configuration classes.

#[EnvMatch('APP_NAME', 'title')]
class App implements ConfigInterface
{
    public string $title = 'My App';
}

class Language extends \Laucov\WebFwk\Config\Language
{}

class View extends \Laucov\WebFwk\Config\View
{
    public string $cacheDir = __DIR__ . '/view-cache';
    public string $viewsDir = __DIR__ . '/view-files';
}

// Controller classes.

class FlightController extends AbstractController
{
    protected array $flights = [
        ['id' => 1, 'call_sign' => 'AZU4991', 'aircraft' => 'ATR 72-600'],
        ['id' => 2, 'call_sign' => 'GLO1212', 'aircraft' => 'Boeing 737-7BX'],
        ['id' => 3, 'call_sign' => 'TAM3279', 'aircraft' => 'Airbus A319-132'],
    ];

    public function home(RequestInterface $req): ResponseInterface
    {
        // Get theme.
        $theme_cookie = $req->getCookie('theme');
        $theme = $theme_cookie === null ? null : $theme_cookie->value;

        // Get view content.
        $view = $this->services
            ->view()
            ->getView('view-a')
            ->get([
                'lang' => $this->services->lang(),
                'theme' => $theme,
                'title' => $this->config->getConfig(App::class)->title,
            ]);
        
        // Set response.
        $this->response->setHeaderLine('Content-Type', 'text/html');
        $this->response->setBody($view);

        return $this->response;
    }

    public function list(): ResponseInterface
    {
        $data = ['data' => $this->flights];
        $json = json_encode($data);
        $this->response->setHeaderLine('Content-Type', 'application/json');
        $this->response->setBody($json);
        return $this->response;
    }
}
