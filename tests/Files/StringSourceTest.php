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
     * @uses Covaleski\Framework\Files\StringSource::__construct
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
     * @uses Covaleski\Framework\Files\StringSource::__construct
     */
    public function testMustReadPositiveLenghts(): void
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
