<?php

declare(strict_types=1);

namespace Tests\Http;

use Covaleski\Framework\Data\ArrayReader;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\Data\ArrayReader
 */
class ArrayReaderTest extends TestCase
{
    protected ArrayReader $reader;

    protected function setUp(): void
    {
        $this->reader = new ArrayReader([
            'user' => [
                'name' => 'John Doe',
                'age' => 42,
                'email' => 'john.doe@example.com',
            ],
            'message' => 'Hello, World!',
            'date' => new \DateTime('1970-01-01 12:00:00'),
        ]);
    }

    /**
     * @covers ::hasValue
     * @uses Covaleski\Framework\Data\ArrayReader::__construct
     * @uses Covaleski\Framework\Data\ArrayReader::validateKeys
     */
    public function testCanCheckValue(): void
    {
        // Test with single key.
        $this->assertSame(true, $this->reader->hasValue('message'));
        $this->assertSame(false, $this->reader->hasValue('foobar'));

        // Test with nested keys.
        $this->assertSame(true, $this->reader->hasValue(['user', 'name']));
        $this->assertSame(false, $this->reader->hasValue(['user', 'birth']));

        // Test if is not creating keys when referencing inexistent ones.
        $this->assertSame(
            false,
            $this->reader->hasValue(['user', 'birth', 'month']),
        );
        $this->assertSame(false, $this->reader->hasValue(['user', 'birth']));

        // Test with intermediary keys that are not arrays.
        $actual = $this->reader->hasValue(['user', 'name', 'first']);
        $this->assertSame(false, $actual);
    }

    /**
     * @covers ::__construct
     * @covers ::getArray
     */
    public function testCanGetArray(): void
    {
        $input = [
            'foo' => 'bar',
        ];
        $reader = new ArrayReader($input);
        $output = $reader->getArray();
        $this->assertSame('bar', $output['foo']);
    }

    /**
     * @covers ::__construct
     * @covers ::getValue
     * @uses Covaleski\Framework\Data\ArrayReader::validateKeys
     */
    public function testCanGetValue(): void
    {
        // Test with single key.
        $this->assertSame(
            'Hello, World!',
            $this->reader->getValue('message'),
        );

        // Test with nested keys.
        $this->assertSame(42, $this->reader->getValue(['user', 'age']));
    }

    /**
     * @covers ::hasValue
     * @covers ::validateKeys
     * @uses Covaleski\Framework\Data\ArrayReader::__construct
     */
    public function testMustCheckWithValidKeys(): void
    {
        $this->reader->hasValue(['foo', 0, 'bar']);
        $this->expectException(\InvalidArgumentException::class);
        $this->reader->hasValue(['foo', ['bar', 'baz']]);
    }

    /**
     * @covers ::hasValue
     * @covers ::validateKeys
     * @uses Covaleski\Framework\Data\ArrayReader::__construct
     */
    public function testMustCheckWithAtLeastOneKey(): void
    {
        $this->reader->hasValue(['foo']);
        $this->expectException(\InvalidArgumentException::class);
        $this->reader->hasValue([]);
    }

    /**
     * @covers ::getValue
     * @covers ::validateKeys
     * @uses Covaleski\Framework\Data\ArrayReader::__construct
     */
    public function testMustGetWithValidKeys(): void
    {
        $this->reader->getValue(['foo', 0, 'bar']);
        $this->expectException(\InvalidArgumentException::class);
        $this->reader->getValue(['foo', ['bar', 'baz']]);
    }

    /**
     * @covers ::getValue
     * @covers ::validateKeys
     * @uses Covaleski\Framework\Data\ArrayReader::__construct
     */
    public function testMustGetWithAtLeastOneKey(): void
    {
        $this->reader->getValue(['foo']);
        $this->expectException(\InvalidArgumentException::class);
        $this->reader->getValue([]);
    }
}
