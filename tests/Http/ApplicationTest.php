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
            'just server data' => [
                [
                    // $_SERVER
                    'server' => [
                        'REQUEST_URI' => '/flights',
                        'SERVER_PROTOCOL' => 'HTTP/1.0',
                    ],
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
            'URI parameters' => [
                [
                    // $_SERVER
                    'server' => [
                        'REQUEST_URI' => '/flights',
                        'SERVER_PROTOCOL' => 'HTTP/1.0',
                    ],
                    // $_GET
                    'params' => [
                        'aircraft' => 'A',
                        'page' => [
                            'number' => '2',
                            'length' => '1',
                        ],
                    ],
                ],
                // Expected output.
                'HTTP/1.0 200 OK' . "\n" .
                'Content-Type: application/json' . "\n" .
                'Content-Length: 70' . "\n" .
                '{"data":[' .
                '{"id":3,"call_sign":"TAM3279","aircraft":"Airbus A319-132"}' .
                ']}',
            ],
            'protocol version + cookies' => [
                [
                    // $_COOKIE
                    'cookies' => [
                        'theme' => 'dark',
                    ],
                    // $_SERVER
                    'server' => [
                        'REQUEST_URI' => '/home',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                    ],
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
            'headers + environment' => [
                [
                    // $_ENV
                    'environment' => [
                        'APP_NAME' => 'The App',
                    ],
                    // $_SERVER
                    'server' => [
                        'REQUEST_URI' => '/home',
                        'SERVER_PROTOCOL' => 'HTTP/1.0',
                        'HTTP_X_USER_NAME' => 'John'
                    ],
                ],
                // Expected output.
                "HTTP/1.0 200 OK\n" .
                "Content-Type: text/html\n" .
                "Content-Length: 102\n" .
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
            'request method + POST data + headers' => [
                [
                    // $_SERVER
                    'server' => [
                        'HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded',
                        'REQUEST_METHOD' => 'POST',
                        'REQUEST_URI' => '/flights',
                        'SERVER_PROTOCOL' => 'HTTP/1.0',
                    ],
                    // $_POST
                    'post' => [
                        'call_sign' => 'ARG1246',
                        'aircraft' => 'Boeing 737-81D',
                    ],
                ],
                // Expected output.
                "HTTP/1.0 201 Created\n" .
                "Content-Type: text/plain\n" .
                "Content-Length: 79\n" .
                "Flight created with POST variables:\n" .
                "Call sign: ARG1246\n" .
                "Aircraft: Boeing 737-81D",
            ],
            'request method + JSON + headers' => [
                [
                    // $_SERVER
                    'server' => [
                        'HTTP_CONTENT_TYPE' => 'application/json',
                        'REQUEST_METHOD' => 'POST',
                        'REQUEST_URI' => '/flights',
                        'SERVER_PROTOCOL' => 'HTTP/1.0',
                    ],
                    // php://input
                    'filename' => 'data://text/plain;base64,' . base64_encode(
                        '{"call_sign":"TVR4707","aircraft":"Boeing 747-409"}',
                    ),
                ],
                // Expected output.
                "HTTP/1.0 201 Created\n" .
                "Content-Type: text/plain\n" .
                "Content-Length: 79\n" .
                "Flight created with JSON variables:\n" .
                "Call sign: TVR4707\n" .
                "Aircraft: Boeing 747-409",
            ],
            'header fallback - no fallback' => [
                [
                    // $_SERVER
                    'server' => [
                        'CONTENT_TYPE' => 'application/json',
                        'CONTENT_LENGTH' => '13',
                        'HTTP_AUTHORIZATION' => 'Basic am9objoxMjM0',
                        'HTTP_CONTENT_LENGTH' => '13',
                        'HTTP_CONTENT_TYPE' => 'application/json',
                        'PHP_AUTH_USER' => 'Mary',
                        'PHP_AUTH_PW' => '4321',
                        'REQUEST_METHOD' => 'POST',
                        'REQUEST_URI' => '/test',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                    ],
                    // php://input
                    'filename' => 'data://text/plain;base64,' . base64_encode(
                        '{"foo":"bar"}',
                    ),
                ],
                // Expected output.
                "HTTP/1.1 299 Awesome\n" .
                "Content-Length: 89\n" .
                "Authorization => Basic am9objoxMjM0\n" .
                "Content length => 13\n" .
                "Content type => application/json",
            ],
            'header fallback - content type and length' => [
                [
                    // $_SERVER
                    'server' => [
                        'CONTENT_TYPE' => 'application/json',
                        'CONTENT_LENGTH' => '13',
                        'HTTP_AUTHORIZATION' => 'Basic am9objoxMjM0',
                        'PHP_AUTH_USER' => 'Mary',
                        'PHP_AUTH_PW' => '4321',
                        'REQUEST_METHOD' => 'POST',
                        'REQUEST_URI' => '/test',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                    ],
                    // php://input
                    'filename' => 'data://text/plain;base64,' . base64_encode(
                        '{"foo":"bar"}',
                    ),
                ],
                // Expected output.
                "HTTP/1.1 299 Awesome\n" .
                "Content-Length: 89\n" .
                "Authorization => Basic am9objoxMjM0\n" .
                "Content length => 13\n" .
                "Content type => application/json",
            ],
            'header fallback - basic authorization' => [
                [
                    // $_SERVER
                    'server' => [
                        'HTTP_CONTENT_LENGTH' => '13',
                        'HTTP_CONTENT_TYPE' => 'application/json',
                        'PHP_AUTH_USER' => 'mary',
                        'PHP_AUTH_PW' => '4321',
                        'REQUEST_METHOD' => 'POST',
                        'REQUEST_URI' => '/test',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                    ],
                    // php://input
                    'filename' => 'data://text/plain;base64,' . base64_encode(
                        '{"foo":"bar"}',
                    ),
                ],
                // Expected output.
                "HTTP/1.1 299 Awesome\n" .
                "Content-Length: 89\n" .
                "Authorization => Basic bWFyeTo0MzIx\n" .
                "Content length => 13\n" .
                "Content type => application/json",
            ],
            'header fallback - digest authorization' => [
                [
                    // $_SERVER
                    'server' => [
                        'HTTP_CONTENT_LENGTH' => '13',
                        'HTTP_CONTENT_TYPE' => 'application/json',
                        'PHP_AUTH_DIGEST' => 'username="foo"',
                        'REQUEST_METHOD' => 'POST',
                        'REQUEST_URI' => '/test',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                    ],
                    // php://input
                    'filename' => 'data://text/plain;base64,' . base64_encode(
                        '{"foo":"bar"}',
                    ),
                ],
                // Expected output.
                "HTTP/1.1 299 Awesome\n" .
                "Content-Length: 92\n" .
                "Authorization => Digest username=\"foo\"\n" .
                "Content length => 13\n" .
                "Content type => application/json",
            ],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::createRouter
     * @covers ::getContentOrPost
     * @covers ::getRouter
     * @covers ::getStatusLine
     * @covers ::run
     * @covers ::setConfigClasses
     * @covers ::setCookies
     * @covers ::setEnvironment
     * @covers ::setInputFilename
     * @covers ::setOutputCallables
     * @covers ::setPostVariables
     * @covers ::setServerInfo
     * @covers ::setUriParameters
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
     * @uses Laucov\WebFwk\Providers\ServiceProvider::lang
     * @uses Laucov\WebFwk\Providers\ServiceProvider::view
     * @uses Laucov\WebFwk\Services\LanguageService::__construct
     * @uses Laucov\WebFwk\Services\LanguageService::findMessage
     * @uses Laucov\WebFwk\Services\LanguageService::update
     * @uses Laucov\WebFwk\Services\ViewService::__construct
     * @uses Laucov\WebFwk\Services\ViewService::getView
     * @dataProvider superGlobalsProvider
     */
    public function testCanRunApplication(array $data, string $expected): void
    {
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
                Language::class,
                View::class,
            )
            ->setCookies($data['cookies'] ?? [])
            ->setEnvironment($data['environment'] ?? [])
            ->setInputFilename($data['filename'] ?? 'data://text/plain,')
            ->setOutputCallables(
                $println,
                $println,
                $print,
            )
            ->setPostVariables($data['post'] ?? [])
            ->setServerInfo($data['server'] ?? [])
            ->setUriParameters($data['params'] ?? []);
        
        // Create routes.
        $application
            ->getRouter()
            ->setController(FlightController::class)
            ->setMethodRoute('GET', 'home', 'home')
            ->setMethodRoute('POST', 'test', 'analyze')
            ->pushPrefix('flights')
                ->setMethodRoute('GET', '', 'list')
                ->setMethodRoute('POST', '', 'create');
        
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
{
    public array $data = [
        'en' => [
            'user-greeting' => 'Hello, {0}!',
            'stranger-greeting' => 'Howdy, Stranger!',
        ],
    ];
}

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

    protected array $users = [
        'john' => 'John',
    ];

    public function analyze(RequestInterface $req): ResponseInterface
    {
        $authorization = $req->getHeaderLine('Authorization');
        $content_length = $req->getHeaderLine('Content-Length');
        $content_type = $req->getHeaderLine('Content-Type');

        return $this->response
            ->setStatus(299, 'Awesome')
            ->setBody(<<<TEXT
                Authorization => {$authorization}
                Content length => {$content_length}
                Content type => {$content_type}
                TEXT);
    }

    public function create(RequestInterface $req): ResponseInterface
    {
        // Get data.
        switch ($req->getHeaderLine('Content-Type')) {
            case 'application/x-www-form-urlencoded':
            case 'multipart/form-data':
                $type = 'POST';
                $post = $req->getPostVariables();
                $call_sign = $post->getValue('call_sign');
                $aircraft = $post->getValue('aircraft');
                break;
            case 'application/json':
                $type = 'JSON';
                $json = (string) $req->getBody();
                $data = json_decode($json, true);
                $call_sign = $data['call_sign'];
                $aircraft = $data['aircraft'];
                break;
        }

        // Build response.
        $this->response
            ->setStatus(201, 'Created')
            ->setHeaderLine('Content-Type', 'text/plain')
            ->setBody(<<<TEXT
                Flight created with {$type} variables:
                Call sign: {$call_sign}
                Aircraft: {$aircraft}
                TEXT);

        return $this->response;
    }

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
                'name' => $req->getHeader('X-User-Name'),
                'theme' => $theme,
                'title' => $this->config->getConfig(App::class)->title,
            ]);
        
        // Set response.
        $this->response
            ->setHeaderLine('Content-Type', 'text/html')
            ->setBody($view);

        return $this->response;
    }

    public function list(RequestInterface $req): ResponseInterface
    {
        // Get flights.
        $data = $this->flights;

        // Get URI parameters.
        $params = $req->getParameters();

        // Filter aircraft name.
        $aircraft = $params->getValue('aircraft');
        if (is_string($aircraft)) {
            $data = array_filter($data, function ($a) use ($aircraft) {
                return str_starts_with($a['aircraft'], $aircraft);
            });
        }

        // Paginate.
        $page_num = $params->getValue(['page', 'number']);
        $page_len = $params->getValue(['page', 'length']) ?? 1;
        if ($page_num !== null) {
            $page_num = (int) $page_num;
            $page_len = (int) $page_len;
            $offset = ($page_num - 1) * $page_len;
            $data = array_slice($data, $offset, $page_len);
        }

        // Set response.
        $this->response
            ->setHeaderLine('Content-Type', 'application/json')
            ->setBody(json_encode(['data' => $data]));

        return $this->response;
    }
}
