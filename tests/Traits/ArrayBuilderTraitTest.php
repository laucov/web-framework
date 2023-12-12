<?php

namespace Tests\Web;

use Covaleski\Framework\Traits\ArrayBuilderTrait;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Covaleski\Framework\Traits\ArrayBuilderTrait
 */
class ArrayBuilderTraitTest extends TestCase
{
    /**
     * @var ArrayBuilderTrait
     */
    protected object $object;

    protected function setUp(): void
    {
        $this->object = new class {
            use ArrayBuilderTrait {
                getArrayValue as public;
                setArrayValue as public;
            }
        };
    }

    /**
     * @covers ::getArrayValue
     * @covers ::setArrayValue
     * @covers ::validateArrayKeys
     */
    public function testCanSetAndGetValueWithString(): void
    {
        $array = [];
        $this->assertNull($this->object->getArrayValue($array, 'name', null));
        $this->object->setArrayValue($array, 'name', 'John');
        $value = $this->object->getArrayValue($array, 'name', null);
        $this->assertSame('John', $value);
    }

    /**
     * @covers ::getArrayValue
     * @covers ::setArrayValue
     * @covers ::validateArrayKeys
     */
    public function testCanSetAndGetValueWithArray(): void
    {
        $array = [];
        $keys = ['user', 'name'];
        $this->assertNull($this->object->getArrayValue($array, $keys, null));
        $this->object->setArrayValue($array, $keys, 'John');
        $value = $this->object->getArrayValue($array, $keys, null);
        $this->assertSame('John', $value);
    }

    /**
     * @covers ::getArrayValue
     * @covers ::validateArrayKeys
     */
    public function testMustGetWithIntegerOrStringKeys(): void
    {
        $array = [];
        $keys = ['user', null];
        $this->expectException(\InvalidArgumentException::class);
        $this->object->getArrayValue($array, $keys, 'foobar');
    }

    /**
     * @covers ::setArrayValue
     * @covers ::validateArrayKeys
     */
    public function testMustSetWithIntegerOrStringKeys(): void
    {
        $array = [];
        $keys = ['user', null];
        $this->expectException(\InvalidArgumentException::class);
        $this->object->setArrayValue($array, $keys, 'John');
    }

    /**
     * @covers ::getArrayValue
     * @covers ::validateArrayKeys
     */
    public function testReturnsDefaultValueWhenSearchingNonArray(): void
    {
        $array = [
            'user' => 'John Doe <john.doe@example.com>',
        ];
        $keys = ['user', 'name'];
        $this->assertNull($this->object->getArrayValue($array, $keys, null));
    }
}
