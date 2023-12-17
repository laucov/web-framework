<?php

declare(strict_types=1);

namespace Tests\Http;

use Covaleski\Framework\Data\ArrayBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\Data\ArrayBuilder
 */
class ArrayBuilderTest extends TestCase
{
    protected ArrayBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new ArrayBuilder([
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
     * @covers ::removeValue
     * @uses Covaleski\Framework\Data\ArrayBuilder::__construct
     * @uses Covaleski\Framework\Data\ArrayBuilder::getValue
     * @uses Covaleski\Framework\Data\ArrayBuilder::validateKeys
     */
    public function testCanRemoveValue(): void
    {
        // Test with single key.
        $this->builder->removeValue('message');
        $actual = $this->builder->getValue('message', 'undefined');
        $this->assertSame('undefined', $actual);

        // Test with nested keys.
        $this->builder->removeValue(['user', 'age']);
        $actual = $this->builder->getValue(['user', 'age'], 'undefined');
        $this->assertSame('undefined', $actual);

        // Test with keys that don't exist.
        $this->builder->removeValue(['user', 'roles', 0]);
        $actual = $this->builder->getValue(['user', 'roles', 0], 'undefined');
        $this->assertSame('undefined', $actual);
        // Test if is not creating keys when referencing inexistent ones.
        $actual = $this->builder->getValue(['user', 'roles'], 'undefined');
        $this->assertSame('undefined', $actual);

        // Test with intermediary keys that are not arrays.
        $this->builder->removeValue(['user', 'name', 'first']);
        $actual = $this->builder->getValue(['user', 'name', 'first'], '-');
        $this->assertSame('-', $actual);
    }

    /**
     * @covers ::__construct
     * @covers ::setValue
     * @uses Covaleski\Framework\Data\ArrayBuilder::getValue
     * @uses Covaleski\Framework\Data\ArrayBuilder::validateKeys
     */
    public function testCanSetValue(): void
    {
        // Test with single key.
        $this->builder->setValue('message', 'Hello, Earth!');
        $actual = $this->builder->getValue('message');
        $this->assertSame('Hello, Earth!', $actual);

        // Test with nested keys.
        $this->builder->setValue(['user', 'age'], 58);
        $actual = $this->builder->getValue(['user', 'age']);
        $this->assertSame(58, $actual);

        // Test with keys that don't exist.
        // Will succeed as long as `::setValue` uses references.
        $this->builder->setValue(['user', 'websites', 0], 'john-doe.com');
        $actual = $this->builder->getValue(['user', 'websites', 0]);
        $this->assertSame('john-doe.com', $actual);

        // Test overriding intermediary keys that are not arrays.
        $this->builder->setValue(['user', 'name', 'first'], 'John');
        $actual = $this->builder->getValue(['user', 'name', 'first']);
        $this->assertSame('John', $actual);
    }

    /**
     * @covers ::removeValue
     * @covers ::validateKeys
     * @uses Covaleski\Framework\Data\ArrayBuilder::__construct
     */
    public function testMustRemoveWithValidKeys(): void
    {
        $this->builder->removeValue(['foo', 0, 'bar']);
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->removeValue(['foo', ['bar', 'baz']]);
    }

    /**
     * @covers ::removeValue
     * @covers ::validateKeys
     * @uses Covaleski\Framework\Data\ArrayBuilder::__construct
     */
    public function testMustRemoveWithAtLeastOneKey(): void
    {
        $this->builder->removeValue(['foo']);
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->removeValue([]);
    }

    /**
     * @covers ::setValue
     * @covers ::validateKeys
     * @uses Covaleski\Framework\Data\ArrayBuilder::__construct
     */
    public function testMustSetWithValidKeys(): void
    {
        $this->builder->setValue(['foo', 0, 'bar'], 'baz');
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->setValue(['foo', ['bar', 'baz']], 'baz');
    }

    /**
     * @covers ::setValue
     * @covers ::validateKeys
     * @uses Covaleski\Framework\Data\ArrayBuilder::__construct
     */
    public function testMustSetWithAtLeastOneKey(): void
    {
        $this->builder->setValue(['foo'], 'bar');
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->setValue([], 'bar');
    }

    /**
     * @covers ::getValue
     * @uses Covaleski\Framework\Data\ArrayBuilder::__construct
     * @uses Covaleski\Framework\Data\ArrayBuilder::validateKeys
     */
    public function testReturnsDefaultValues(): void
    {
        // Test with default fallback value.
        $this->assertNull($this->builder->getValue('id'));
        $this->assertNull($this->builder->getValue(['user', 'id']));
        $this->assertNull($this->builder->getValue(['date', 'id']));
        
        // Test with custom fallback value.
        $default_value = 'Not found';
        $this->assertSame(
            $default_value,
            $this->builder->getValue('id', $default_value),
        );
        $this->assertSame(
            $default_value,
            $this->builder->getValue(['user', 'id'], $default_value),
        );
        $this->assertSame(
            $default_value,
            $this->builder->getValue(['date', 'id'], $default_value),
        );
    }
}
