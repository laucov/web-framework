<?php

declare(strict_types=1);

namespace Tests\Files;

use Covaleski\Framework\Files\StringSource;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\Files\StringSource
 * 
 * @todo Make tests with non-readable resources.
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
    public function testCanPerformReadOperations(): void
    {
        // Get size.
        $size_a = $this->sourceA->getSize();
        $size_b = $this->sourceB->getSize();
        $this->assertSame(44, $size_a);
        $this->assertSame(44, $size_b);
        
        // Read all content.
        $this->assertSame($this->text, $this->sourceA->read($size_a));
        $this->assertSame($this->text, $this->sourceB->read($size_b));
        $this->assertSame('', $this->sourceA->read($size_a));
        $this->assertSame('', $this->sourceB->read($size_b));
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
    public function testMustInstantiateWithStringOrResource(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new StringSource(['Lorem', 'ipsum']);
        $this->expectException(\InvalidArgumentException::class);
        new StringSource(12345);
        $this->expectException(\InvalidArgumentException::class);
        new StringSource(new StringSource('Lorem ipsum'));
        $this->expectException(\InvalidArgumentException::class);
        new StringSource(null);
    }
}
