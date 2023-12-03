<?php

declare(strict_types=1);

namespace Tests\Files;

use Covaleski\Framework\Files\StringSource;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\Files\StringSource
 */
final class StringSourceTest extends TestCase
{
    private StringSource $sourceA;

    private StringSource $sourceB;

    private string $text;

    protected function setUp(): void
    {
        // Create text.
        $this->text = 'The quick brown fox jumps over the lazy dog.';

        // Get source from string.
        $this->sourceA = new StringSource($this->text);
        // Get source from resource.
        $resource = fopen('data://text/plain,' . $this->text, 'r');
        $this->sourceB = new StringSource($resource);
    }

    /**
     * @covers ::getSize
     * @covers ::read
     * @covers ::seek
     * @covers ::tell
     * @uses Covaleski\Framework\Files\StringSource::__construct
     */
    public function testCanRead(): void
    {
        // Get size.
        $size_a = $this->sourceA->getSize();
        $size_b = $this->sourceB->getSize();
        $this->assertSame(44, $size_a);
        $this->assertSame(44, $size_b);
        
        // Read all content.
        $this->assertSame($this->text, $this->sourceA->read($size_a));
        $this->assertSame($this->text, $this->sourceB->read($size_b));

        // Try reading after EOF.
        $this->assertSame('', $this->sourceA->read($size_a));
        $this->assertSame('', $this->sourceB->read($size_b));

        // Ensure pointer is at EOF.
        $this->assertSame(44, $this->sourceA->tell());
        $this->assertSame(44, $this->sourceB->tell());

        // Move pointer.
        $this->sourceA->seek(0);
        $this->sourceB->seek(0);
        $this->assertSame(0, $this->sourceA->tell());
        $this->assertSame(0, $this->sourceB->tell());
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
     * @uses Covaleski\Framework\Files\StringSource::__construct
     */
    public function testMustReadValidFileLenght(): void
    {
        // Test using negative positions.
        $this->expectException(\InvalidArgumentException::class);
        $this->sourceB->read(-1);
    }

    /**
     * @covers ::read
     * @uses Covaleski\Framework\Files\StringSource::__construct
     */
    public function testMustReadValidStringLenght(): void
    {
        // Test using negative positions.
        $this->expectException(\InvalidArgumentException::class);
        $this->sourceA->read(-1);
    }

    /**
     * @covers ::seek
     * @uses Covaleski\Framework\Files\StringSource::getSize
     * @uses Covaleski\Framework\Files\StringSource::__construct
     */
    public function testMustSeekValidFilePosition(): void
    {
        // Test using after EOF positions.
        $this->expectException(\InvalidArgumentException::class);
        $this->sourceB->seek(1024);
    }

    /**
     * @covers ::seek
     * @uses Covaleski\Framework\Files\StringSource::getSize
     * @uses Covaleski\Framework\Files\StringSource::__construct
     */
    public function testMustSeekValidStringPosition(): void
    {
        // Test using after EOF positions.
        $this->expectException(\InvalidArgumentException::class);
        $this->sourceA->seek(1024);
    }
}
