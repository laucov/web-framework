<?php

declare(strict_types=1);

namespace Tests\Cli;

use Covaleski\Framework\Cli\Printer;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\Cli\Printer
 */
final class PrinterTest extends TestCase
{
    protected Printer $printer;

    protected function setUp(): void
    {
        $this->printer = new Printer();
    }

    /**
     * @covers ::colorize
     */
    public function testCanColorizeText(): void
    {
        // Check color avaibility.
        $colors = [
            Printer::BG_BLUE,
            Printer::BG_CYAN,
            Printer::BG_GREEN,
            Printer::BG_MAGENTA,
            Printer::BG_RED,
            Printer::BG_YELLOW,
            Printer::TEXT_BLUE,
            Printer::TEXT_CYAN,
            Printer::TEXT_GREEN,
            Printer::TEXT_MAGENTA,
            Printer::TEXT_RED,
            Printer::TEXT_YELLOW,
        ];

        // Make example text.
        $text = 'Hello, World!';

        // Test without colors.
        $result = $this->printer->colorize($text, []);
        $this->assertSame('\e[0mHello, World!\e[0m', $result);

        // Test single color.
        $colors = [Printer::TEXT_RED];
        $result = $this->printer->colorize($text, $colors);
        $this->assertSame('\e[0;31mHello, World!\e[0m', $result);

        // Test multiple colors.
        $colors = [Printer::BG_RED, Printer::TEXT_GREEN];
        $result = $this->printer->colorize($text, $colors);
        $this->assertSame('\e[0;41;32mHello, World!\e[0m', $result);

        // Fail to use non-integer colors.
        $colors = [Printer::BG_RED, 'invalid color'];
        $this->expectException(\InvalidArgumentException::class);
        $this->printer->colorize($text, $colors);
    }

    /**
     * @covers ::print
     * @covers ::printLine
     * @uses Covaleski\Framework\Cli\Printer::colorize
     */
    public function testCanPrintText(): void
    {
        // Make example text.
        $colors = [Printer::TEXT_CYAN, Printer::BG_MAGENTA];
        $text = 'Hello, World!';

        // Print with and without line feed.
        $expected = $this->printer->colorize($text, $colors)
            . $this->printer->colorize($text, $colors)
            . PHP_EOL;
        $this->expectOutputString($expected);
        $this->assertSame(
            $this->printer,
            $this->printer->print($text, $colors),
        );
        $this->assertSame(
            $this->printer,
            $this->printer->printLine($text, $colors),
        );
    }
}
