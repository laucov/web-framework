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

namespace Tests\Integration\Providers;

use Laucov\Db\Data\Driver\DriverFactory;
use Laucov\WebFwk\Config\Authorization;
use Laucov\WebFwk\Config\Database;
use Laucov\WebFwk\Config\Language;
use Laucov\WebFwk\Config\Session;
use Laucov\WebFwk\Config\Smtp;
use Laucov\WebFwk\Config\View;
use Laucov\WebFwk\Models\UserAuthnMethodModel;
use Laucov\WebFwk\Models\UserModel;
use Laucov\WebFwk\Providers\ConfigProvider;
use Laucov\WebFwk\Security\Authentication\AuthnFactory;
use Laucov\WebFwk\Services\FileSessionService;
use Laucov\WebFwk\Services\PhpMailerSmtpService;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Providers\ConfigProvider
 */
class ConfigProviderTest extends TestCase
{
    /**
     * Provides setup data for the provider.
     * 
     * Also provides expectations for the configuration objects.
     */
    public function configSetupProvider(): array
    {
        // Set configuration class names.
        $config_class_names = [
            Authorization::class,
            Database::class,
            Language::class,
            Session::class,
            Smtp::class,
            View::class,
        ];

        return [
            // Test without environment variables.
            [
                [],
                $config_class_names,
                [
                    Authorization::class => [
                        'authnFactory' => AuthnFactory::class,
                        'userAuthnMethodModel' => UserAuthnMethodModel::class,
                        'userModel' => UserModel::class,
                    ],
                    Database::class => [
                        'defaultConnection' => '',
                        'defaultConnections' => [],
                        'driverFactoryName' => DriverFactory::class,
                    ],
                    Language::class => [
                        'defaultLocale' => 'en',
                        'data' => [],
                        'directories' => [],
                        'redirects' => [],
                        'supportedLocales' => [],
                    ],
                    Session::class => [
                        'path' => '',
                        'service' => FileSessionService::class,
                    ],
                    Smtp::class => [
                        'fromAddress' => null,
                        'fromName' => null,
                        'host' => null,
                        'password' => null,
                        'port' => 465,
                        'service' => PhpMailerSmtpService::class,
                        'user' => null,
                    ],
                    View::class => [
                        'cacheDir' => '',
                        'viewsDir' => '',
                    ],
                ],
            ],
            // Test with environment variables.
            [
                [
                    'APP_DATABASE_DEFAULT_CONN' => 'primary',
                    'APP_DATABASE_DEFAULT_CONNS' => [
                        'primary' => ['sqlite::memory:'],
                        'secondary' => ['mysql:host=localhost;dbname=db', 'john', '1234'],
                    ],
                    'APP_SESSION_PATH' => '/path/to/sessions',
                    'APP_SMTP_FROM_ADDRESS' => 'no-reply@foobar.co.uk',
                    'APP_SMTP_FROM_NAME' => 'Foobar Company',
                    'APP_SMTP_HOST' => 'smtp.fmail.com',
                    'APP_SMTP_PASSWORD' => '1234',
                    'APP_SMTP_PORT' => '466',
                    'APP_SMTP_USER' => 'system@foobar.co.uk',
                    'APP_VIEW_CACHE_DIR' => '/path/to/cache',
                ],
                $config_class_names,
                [
                    Authorization::class => [
                        'authnFactory' => AuthnFactory::class,
                        'userAuthnMethodModel' => UserAuthnMethodModel::class,
                        'userModel' => UserModel::class,
                    ],
                    Database::class => [
                        'defaultConnection' => 'primary',
                        'defaultConnections' => [
                            'primary' => ['sqlite::memory:'],
                            'secondary' => ['mysql:host=localhost;dbname=db', 'john', '1234'],
                        ],
                        'driverFactoryName' => DriverFactory::class,
                    ],
                    Language::class => [
                        'defaultLocale' => 'en',
                        'data' => [],
                        'directories' => [],
                        'redirects' => [],
                        'supportedLocales' => [],
                    ],
                    Session::class => [
                        'path' => '/path/to/sessions',
                        'service' => FileSessionService::class,
                    ],
                    Smtp::class => [
                        'fromAddress' => 'no-reply@foobar.co.uk',
                        'fromName' => 'Foobar Company',
                        'host' => 'smtp.fmail.com',
                        'password' => '1234',
                        'port' => 466,
                        'service' => PhpMailerSmtpService::class,
                        'user' => 'system@foobar.co.uk',
                    ],
                    View::class => [
                        'cacheDir' => '/path/to/cache',
                        'viewsDir' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * @coversNothing
     * @dataProvider configSetupProvider
     */
    public function testCanUseCustomEnvironmentVariables(
        array $environment,
        array $config_class_names,
        array $expected,
    ): void {
        // Create the provider instance.
        $provider = new ConfigProvider($environment);

        // Add configuration.
        foreach ($config_class_names as $class_name) {
            $provider->addConfig($class_name);
        }

        // Assert configurations.
        foreach ($expected as $class_name => $properties) {
            $config = $provider->getConfig($class_name);
            foreach ($properties as $name => $value) {
                $this->assertObjectHasProperty($name, $config);
                $this->assertSame($value, $config->{$name} ?? null);
            }
        }
    }
}
