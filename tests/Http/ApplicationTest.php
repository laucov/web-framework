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

use Laucov\Http\Message\ResponseInterface;
use Laucov\WebFwk\Http\AbstractController;
use Laucov\WebFwk\Http\Application;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Http\Application
 */
class ApplicationTest extends TestCase
{
    public function superGlobalsProvider(): array
    {
        return [
            0 => [
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
            1 => [
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
                [
                    'Authorization: john:1234',
                ],
                // $_POST
                [],
                // $_SERVER
                [
                    'REQUEST_URI' => '/home',
                    'SERVER_PROTOCOL' => 'HTTP/1.1',
                ],
                // Expected output.
                'HTTP/1.1 200 OK' .
                'Content-Type: text/html' .
                'Content-Length: text/html' .
                <<<HTML
                    <!DOCTYPE html>
                    <html>
                    <body class="body--dark">
                    <p>Hello, John!</p>
                    </body>
                    </html>
                    HTML,
            ],
        ];
    }

    /**
     * @covers ::__construct
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
     * @uses Laucov\WebFwk\Providers\ServiceDependencyRepository::setConfigProvider
     * @uses Laucov\WebFwk\Providers\ServiceProvider::__construct
     * @dataProvider superGlobalsProvider
     */
    public function testCanRunApplication(
        array $cookies,
        array $environment,
        string $input_filename,
        array $get,
        array $headers,
        array $post,
        array $server,
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

class View extends \Laucov\WebFwk\Config\View
{
    public string $cacheDir = __DIR__ . '/view-cache';
    public string $viewsDir = __DIR__ . '/view-files';
}

class Language extends \Laucov\WebFwk\Config\Language
{}

// Controller classes.

class FlightController extends AbstractController
{
    protected array $flights = [
        ['id' => 1, 'call_sign' => 'AZU4991', 'aircraft' => 'ATR 72-600'],
        ['id' => 2, 'call_sign' => 'GLO1212', 'aircraft' => 'Boeing 737-7BX'],
        ['id' => 3, 'call_sign' => 'TAM3279', 'aircraft' => 'Airbus A319-132'],
    ];

    public function home(): ResponseInterface
    {
        $view = $this->services
            ->view()
            ->getView('view-a')
            ->get([]);
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
