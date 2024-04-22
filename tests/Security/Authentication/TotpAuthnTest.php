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

namespace Tests\Security;

use Laucov\WebFwk\Security\Authentication\TotpAuthn;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Security\Authentication\TotpAuthn
 */
class TotpAuthnTest extends TestCase
{
    /**
     * Provides authentication tests.
     */
    public function authnTestProvider(): array
    {
        return [
            [
                // Configuration
                [
                    'digits' => 6,
                    'offset' => 0,
                    'secret' => 'abcdefghij',
                    'step' => 30,
                ],
                // Tests
                [
                    [true, 1325419200, ['password' => '274268']],
                    [false, 1325419200, ['password' => '954167']],
                    [true, 1325419235, ['password' => '935599']],
                    [false, 1325419235, ['password' => '288412']],
                    [true, 1325419261, ['password' => '478417']],
                    [false, 1325419261, ['password' => '274268']],
                    [false, 1325419261, ['password' => '417']],
                    [false, 1325419261, []],
                    [false, 1325419261, ['password' => ['478417']]],
                ],
            ],
        ];
    }

    /**
     * @covers ::configure
     * @covers ::request
     * @covers ::validate
     * @dataProvider authnTestProvider
     */
    public function testCanAuthenticate(array $config, array $tests): void
    {
        // Create and configure the authentication object.
        $authn = new TotpAuthn();
        $authn->configure($config);

        // Run tests for this configuration.
        foreach ($tests as [$expected, $time, $data]) {
            $authn->request();
            Timestamp::$value = $time;
            $this->assertSame($expected, $authn->validate($data));
        }
    }

    protected function setUp(): void
    {
        Timestamp::$value = null;
    }

    protected function tearDown(): void
    {
        Timestamp::$value = null;
    }
}

class Timestamp
{
    public static null|int $value = null;
}

namespace Covaleski\Otp;

function time(): int
{
    return \Tests\Security\Timestamp::$value ?? \time();
}
