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

namespace Laucov\WebFwk\Http;

use Laucov\Http\Message\IncomingRequest;
use Laucov\Http\Message\RequestInterface;
use Laucov\Http\Message\ResponseInterface;
use Laucov\Http\Server\ServerInfo;
use Laucov\WebFwk\Providers\ConfigProvider;
use Laucov\WebFwk\Providers\ServiceProvider;

/**
 * Runs a web application from the given settings and superglobal variables.
 */
class Application
{
    /**
     * Content types which cause PHP to fill `$_POST`.
     */
    public const POST_CONTENT_TYPES = [
        'application/x-www-form-urlencoded',
        'multipart/form-data',
    ];

    /**
     * Configuration classes.
     * 
     * @var array<string>
     */
    protected array $configClasses = [];

    /**
     * Environment variables.
     */
    protected array $environment = [];

    /**
     * Request input filename.
     */
    protected string $inputFilename = 'data://text/plain,';

    /**
     * Callables used to output different parts of the response.
     * 
     * Must contain three keys: `status_line`, `header` and `body`.
     * 
     * @var array<string, callable>
     */
    protected array $outputCallables = [];

    /**
     * Parsed POST variables.
     */
    protected array $postVariables = [];

    /**
     * Request cookies (key-value pairs).
     */
    protected array $requestCookies = [];

    /**
     * Request headers (key-value pairs).
     */
    protected array $requestHeaders = [];

    /**
     * Router.
     */
    protected ControllerRouter $router;

    /**
     * Server information object.
     */
    protected ServerInfo $server;

    /**
     * Parsed URI parameters (e.g. `$_GET`).
     */
    public array $uriParameters = [];

    /**
     * Create the application instance.
     */
    public function __construct()
    {
    }

    /**
     * Get the application router.
     */
    public function getRouter(): ControllerRouter
    {
        $this->router ??= $this->createRouter();
        return $this->router;
    }

    /**
     * Run the application for the current data and settings.
     */
    public function run(): void
    {
        // Create request.
        $request = new IncomingRequest(
            content_or_post: $this->getContentOrPost(),
            headers: $this->requestHeaders,
            protocol_version: $this->server->getProtocolVersion(),
            method: $this->server->get('REQUEST_METHOD') ?? 'GET',
            uri: $this->server->getRequestUri(),
            parameters: $this->uriParameters,
            cookies: $this->requestCookies,
        );

        // Find route.
        $route = $this->getRouter()->findRoute($request);
        // if ($route === null) {
        //     return;
        // }

        // Get response.
        $response = $route->run();

        // Get data.
        $status = $this->getStatusLine($request, $response);
        $header_names = $response->getHeaderNames();
        $body = $response->getBody();

        // Output status line.
        $this->outputCallables['status_line']($status);

        // Output headers.
        foreach ($header_names as $name) {
            $header_lines = $response->getHeaderLines($name);
            foreach ($header_lines as $line) {
                $this->outputCallables['header']("{$name}: {$line}");
            }
        }

        // Output body.
        if ($body !== null) {
            // Output content length.
            $length = $body->getSize();
            $this->outputCallables['header']("Content-Length: {$length}");
            // Output content.
            for ($i = 0; $i < ceil($length / 4096); $i++) {
                $this->outputCallables['body']($body->read(4096));
            }
        }
    }

    /**
     * Set configuration classes to use.
     */
    public function setConfigClasses(string ...$classes): static
    {
        $this->configClasses = $classes;
        return $this;
    }

    /**
     * Set cookie values.
     */
    public function setCookies(array $values): static
    {
        $this->requestCookies = $values;
        return $this;
    }

    /**
     * Set environment variables.
     */
    public function setEnvironment(array $values): static
    {
        $this->environment = $values;
        return $this;
    }

    /**
     * Set the request body content filename.
     */
    public function setInputFilename(string $filename): static
    {
        $this->inputFilename = $filename;
        return $this;
    }

    /**
     * Set application output callables.
     */
    public function setOutputCallables(
        callable $send_status_line,
        callable $send_header,
        callable $send_body,
    ): static {
        $this->outputCallables = [
            'status_line' => $send_status_line,
            'header' => $send_header,
            'body' => $send_body,
        ];
        return $this;
    }

    /**
     * Set parsed URI parameters.
     */
    public function setUriParameters(array $values): static
    {
        $this->uriParameters = $values;
        return $this;
    }

    /**
     * Set POST variables.
     */
    public function setPostVariables(array $values): static
    {
        $this->postVariables = $values;
        return $this;
    }

    /**
     * Set server information.
     */
    public function setServerInfo(array $values): static
    {
        // Set headers.
        foreach ($values as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $this->requestHeaders[$name] = $value;
            }
        }

        // Get missing Content-Type header.
        if (
            !array_key_exists('content-type', $this->requestHeaders)
            && array_key_exists('CONTENT_TYPE', $values)
        ) {
            $this->requestHeaders['content-type'] = $values['CONTENT_TYPE'];
        }

        // Get missing Content-Length header.
        if (
            !array_key_exists('content-length', $this->requestHeaders)
            && array_key_exists('CONTENT_LENGTH', $values)
        ) {
            $this->requestHeaders['content-length'] = $values['CONTENT_LENGTH'];
        }

        // Get missing Authorization header.
        if (!array_key_exists('authorization', $this->requestHeaders)) {
            if (array_key_exists('PHP_AUTH_USER', $values)) {
                // Get Basic authorization.
                $user = $values['PHP_AUTH_USER'];
                $password = $values['PHP_AUTH_PW'] ?? '';
                $authz = base64_encode("{$user}:{$password}");
                $this->requestHeaders['authorization'] = "Basic {$authz}";
            } elseif (array_key_exists('PHP_AUTH_DIGEST', $values)) {
                // Get Digest authorization.
                $digest = $values['PHP_AUTH_DIGEST'];
                $this->requestHeaders['authorization'] = "Digest {$digest}";
            }
        }

        // Set server info.
        $this->server = new ServerInfo($values);

        return $this;
    }

    protected function createRouter(): ControllerRouter
    {
        // Setup configuration provider.
        $config = new ConfigProvider($this->environment);
        foreach ($this->configClasses as $class_name) {
            $config->addConfig($class_name);
        }

        // Setup service provider.
        $services = new ServiceProvider($config);

        // Setup router.
        $router = new ControllerRouter();
        $router->setProviders($config, $services);

        return $router;
    }

    /**
     * Get the incoming request content (POST variables or input file pointer).
     * 
     * @return array|resource
     */
    protected function getContentOrPost(): mixed
    {
        // Check if is a POST request.
        $method = strtoupper($this->server->get('REQUEST_METHOD'));
        if ($method !== 'POST') {
            return fopen($this->inputFilename, 'r');
        }

        // Check if is a form POST.
        $type = $this->requestHeaders['content-type'] ?? '';
        if (!in_array($type, static::POST_CONTENT_TYPES, true)) {
            return fopen($this->inputFilename, 'r');
        }

        return $this->postVariables;
    }

    /**
     * Build the status line from a response object.
     */
    protected function getStatusLine(
        RequestInterface $request,
        ResponseInterface $response,
    ): string {
        $protocol_version = $response->getProtocolVersion()
            ?? $request->getProtocolVersion();
        $code = $response->getStatusCode();
        $text = $response->getStatusText();

        return "HTTP/{$protocol_version} {$code} {$text}";
    }
}
