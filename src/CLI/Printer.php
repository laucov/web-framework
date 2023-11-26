<?php

namespace Covaleski\Framework\CLI;

/**
 * Provides CLI output utilities.
 */
class Printer
{
    /**
     * Blue background color.
     */
    public const BG_BLUE = 44;

    /**
     * Cyan background color.
     */
    public const BG_CYAN = 46;

    /**
     * Green background color.
     */
    public const BG_GREEN = 42;

    /**
     * Magenta background color.
     */
    public const BG_MAGENTA = 45;

    /**
     * Red background color.
     */
    public const BG_RED = 41;

    /**
     * Yellow background color.
     */
    public const BG_YELLOW = 43;

    /**
     * Blue text color.
     */
    public const TEXT_BLUE = 34;

    /**
     * Cyan text color.
     */
    public const TEXT_CYAN = 36;

    /**
     * Green text color.
     */
    public const TEXT_GREEN = 32;

    /**
     * Magenta text color.
     */
    public const TEXT_MAGENTA = 35;

    /**
     * Red text color.
     */
    public const TEXT_RED = 31;

    /**
     * Yellow text color.
     */
    public const TEXT_YELLOW = 33;

    /**
     * Apply ANSI colors to the given text.
     */
    public function colorize(string $text, array $colors = []): string
    {
        $result = '\e[0';
        foreach ($colors as $color) {
            $result .= ';' . $color;
        }
        $result .= 'm' . $text . '\e[0m';

        return $result;
    }

    /**
     * Print the given text using the given colors.
     */
    public function print(string $text, array $colors = []): void
    {
        echo $this->colorize($text, $colors);
    }

    /**
     * Print an individual line with the given text using the given colors.
     */
    public function printLine(string $text, array $colors = []): void
    {
        $this->print($text, $colors);
        echo PHP_EOL;
    }
}
