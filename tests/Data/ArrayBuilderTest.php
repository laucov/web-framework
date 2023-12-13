<?php

declare(strict_types=1);

namespace Tests\Http;

use Covaleski\Framework\Data\ArrayBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\Data\ArrayBuilder
 * 
 * @todo ::getArray
 * @todo ::setValue
 * @todo ::removeValue
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
     * @covers ::__construct
     * @covers ::getValue
     * @uses Covaleski\Framework\Data\ArrayBuilder::validateKeys
     */
    public function testCanGetValue(): void
    {
        $this->assertSame(
            'Hello, World!',
            $this->builder->getValue('message'),
        );
        $this->assertSame(42, $this->builder->getValue(['user', 'age']));
    }

    /**
     * @covers ::getValue
     * @covers ::validateKeys
     * @uses Covaleski\Framework\Data\ArrayBuilder::__construct
     */
    public function testMustGetWithValidKeys(): void
    {
        $this->builder->getValue(['foo', 0, 'bar']);
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->getValue(['foo', ['bar', 'baz']]);
    }

    /**
     * @covers ::getValue
     * @covers ::validateKeys
     * @uses Covaleski\Framework\Data\ArrayBuilder::__construct
     */
    public function testMustGetWithAtLeastOneKey(): void
    {
        $this->builder->getValue(['foo']);
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->getValue([]);
    }

    /**
     * @covers ::getValue
     * @uses Covaleski\Framework\Data\ArrayBuilder::__construct
     * @uses Covaleski\Framework\Data\ArrayBuilder::validateKeys
     */
    public function testReturnsDefaultValues(): void
    {
        $this->assertNull($this->builder->getValue('id'));
        $this->assertNull($this->builder->getValue(['user', 'id']));
        $this->assertNull($this->builder->getValue(['date', 'id']));
        
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
