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
     * Callables used to output different parts of the response.
     * 
     * Must contain three keys: `status_line`, `header` and `body`.
     * 
     * @var array<string, callable>
     */
    protected array $outputCallables = [];
    
    /**
     * Router.
     */
    protected ControllerRouter $router;

    /**
     * Server information object.
     */
    protected ServerInfo $server;

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
            '',
            uri: $this->server->getRequestUri(),
        );

        // Find route.
        $route = $this->getRouter()->findRoute($request);
        // if ($route === null) {
        //     return;
        // }

        // Get response.
        $response = $route->run();

        // Get data.
        $status = $this->getStatusLine($response);
        $header_names = $response->getHeaderNames();
        $body = $response->getBody();

        // Output headers.
        $this->outputCallables['status_line']($status);
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
            for ($i = 0; $i < ceil($length/4096); $i++) {
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
     * Set request headers.
     */
    public function setHeaders(array $headers): static
    {
        return $this;
    }

    /**
     * Set the request body content filename.
     */
    public function setInputFilename(string $filename): static
    {
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
    public function setParameters(array $values): static
    {
        return $this;
    }

    /**
     * Set POST variables.
     */
    public function setPostVariables(array $values): static
    {
        return $this;
    }

    /**
     * Set server information.
     */
    public function setServerInfo(array $values): static
    {
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
     * Build the status line from a response object.
     */
    protected function getStatusLine(ResponseInterface $response): string
    {
        $protocol_version = $response->getProtocolVersion() ?? '1.0';
        $code = $response->getStatusCode();
        $text = $response->getStatusText();

        return "HTTP/{$protocol_version} {$code} {$text}";
    }
}
