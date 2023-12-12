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
                removeArrayValue as public;
                setArrayValue as public;
            }
        };
    }

    /**
     * @covers ::getArrayValue
     * @covers ::removeArrayValue
     * @covers ::setArrayValue
     * @covers ::validateArrayKeys
     */
    public function testCanUseMultipleKeys(): void
    {
        $array = [];
        $keys = ['user', 'name'];
        $this->assertNull($this->object->getArrayValue($array, $keys, null));
        $this->object->setArrayValue($array, $keys, 'John');
        $value = $this->object->getArrayValue($array, $keys, null);
        $this->assertSame('John', $value);
        $this->object->removeArrayValue($array, $keys);
        $this->assertNull($this->object->getArrayValue($array, $keys, null));
        $this->assertCount(1, $array);
    }

    /**
     * @covers ::getArrayValue
     * @covers ::removeArrayValue
     * @covers ::setArrayValue
     * @covers ::validateArrayKeys
     */
    public function testCanUseSingleKey(): void
    {
        $array = [];
        $this->assertNull($this->object->getArrayValue($array, 'name', null));
        $this->object->setArrayValue($array, 'name', 'John');
        $value = $this->object->getArrayValue($array, 'name', null);
        $this->assertSame('John', $value);
        $this->object->removeArrayValue($array, 'name');
        $this->assertNull($this->object->getArrayValue($array, 'name', null));
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
     * @covers ::removeArrayValue
     * @covers ::validateArrayKeys
     */
    public function testMustRemoveWithIntegerOrStringKeys(): void
    {
        $array = [];
        $keys = ['user', null];
        $this->expectException(\InvalidArgumentException::class);
        $this->object->removeArrayValue($array, $keys);
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
     * @covers ::validateArrayKeys
     * @uses Covaleski\Framework\Traits\ArrayBuilderTrait::getArrayValue
     */
    public function testMustUseNonEmptyKeyArray(): void
    {
        $array = [];
        $this->expectException(\InvalidArgumentException::class);
        $this->object->getArrayValue($array, [], null);
    }

    /**
     * @covers ::getArrayValue
     * @uses Covaleski\Framework\Traits\ArrayBuilderTrait::validateArrayKeys
     */
    public function testReturnsDefaultValueWhenSearchingNonArray(): void
    {
        $array = [
            'user' => 'John Doe <john.doe@example.com>',
        ];
        $keys = ['user', 'name'];
        $this->assertNull($this->object->getArrayValue($array, $keys, null));
    }

    /**
     * @covers ::removeArrayValue
     * @uses Covaleski\Framework\Traits\ArrayBuilderTrait::validateArrayKeys
     */
    public function testWillIgnoreNonExistentKeysWhenRemoving(): void
    {
        $array = [];
        $this->expectNotToPerformAssertions();
        $this->object->removeArrayValue($array, ['user', 'name']);
    }
}
