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

namespace Tests\Files;

use Laucov\WebFramework\Files\StringSource;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Files\StringSource
 */
final class StringSourceTest extends TestCase
{
    private StringSource $sourceA;

    private StringSource $sourceB;

    private StringSource $sourceC;

    private string $text;

    protected function setUp(): void
    {
        // Create text.
        $this->text = 'The quick brown fox jumps over the lazy dog.';

        // Get source from string.
        $this->sourceA = new StringSource($this->text);
        // Get source from custom resource.
        $resource = fopen('data://text/plain,' . $this->text, 'r');
        $this->sourceB = new StringSource($resource);
        // Get source from file.
        $this->sourceC = new StringSource(fopen(__FILE__, 'r'));
    }

    /**
     * @covers ::getSize
     * @covers ::read
     * @covers ::seek
     * @covers ::tell
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     */
    public function testCanRead(): void
    {
        // Set file resource expected data.
        $contents = file_get_contents(__FILE__);
        $filesize = filesize(__FILE__);

        // Get size.
        $size_a = $this->sourceA->getSize();
        $size_b = $this->sourceB->getSize();
        $size_c = $this->sourceC->getSize();
        $this->assertSame(44, $size_a);
        $this->assertSame(44, $size_b);
        $this->assertSame($filesize, $size_c);

        // Read all content.
        $this->assertSame($this->text, $this->sourceA->read($size_a));
        $this->assertSame($this->text, $this->sourceB->read($size_b));
        $this->assertSame($contents, $this->sourceC->read($size_c));

        // Try reading after EOF.
        $this->assertSame('', $this->sourceA->read($size_a));
        $this->assertSame('', $this->sourceB->read($size_b));
        $this->assertSame('', $this->sourceC->read($size_c));

        // Ensure pointer is at EOF.
        $this->assertSame(44, $this->sourceA->tell());
        $this->assertSame(44, $this->sourceB->tell());
        $this->assertSame($filesize, $this->sourceC->tell());

        // Move pointer.
        $this->sourceA->seek(0);
        $this->sourceB->seek(0);
        $this->sourceC->seek(0);
        $this->assertSame(0, $this->sourceA->tell());
        $this->assertSame(0, $this->sourceB->tell());
        $this->assertSame(0, $this->sourceC->tell());
    }

    /**
     * @covers ::__construct
     */
    public function testMustInstantiateWithResourceOrString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new StringSource(['The', 'quick', 'brown', 'fox']);
    }

    /**
     * @covers ::read
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     */
    public function testMustReadPositiveLenghts(): void
    {
        // Test using negative positions.
        $this->expectException(\InvalidArgumentException::class);
        $this->sourceA->read(-1);
    }

    /**
     * @covers ::seek
     * @uses Laucov\WebFramework\Files\StringSource::getSize
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     */
    public function testMustSeekValidFilePosition(): void
    {
        // Test using after EOF positions.
        $this->expectException(\InvalidArgumentException::class);
        $this->sourceB->seek(1024);
    }

    /**
     * @covers ::seek
     * @uses Laucov\WebFramework\Files\StringSource::getSize
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     */
    public function testMustSeekValidStringPosition(): void
    {
        // Test using after EOF positions.
        $this->expectException(\InvalidArgumentException::class);
        $this->sourceA->seek(1024);
    }
}
