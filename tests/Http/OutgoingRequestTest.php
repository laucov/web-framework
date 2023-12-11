<?php

declare(strict_types=1);

namespace Tests\Http;

use Covaleski\Framework\Http\OutgoingRequest;
use Covaleski\Framework\Web\Uri;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\Http\OutgoingRequest
 * @todo ::addParameter
 * @todo ::setParameter
 */
class OutgoingRequestTest extends TestCase
{
    protected OutgoingRequest $request;

    protected function setUp(): void
    {
        $this->request = new OutgoingRequest();
    }

    /**
     * @covers ::getMethod
     * @covers ::setMethod
     */
    public function testCanSetMethod(): void
    {
        $this->request->setMethod('PUT');
        $this->assertSame('PUT', $this->request->getMethod());
        $this->request->setMethod('get');
        $this->assertSame('GET', $this->request->getMethod());
        $this->request->setMethod('PaTcH');
        $this->assertSame('PATCH', $this->request->getMethod());
    }

    /**
     * @covers ::getParameter
     * @covers ::setParameter
     * @uses Covaleski\Framework\Http\Traits\RequestTrait::getParameter
     * @uses Covaleski\Framework\Http\Traits\RequestTrait::getParameterList
     */
    public function testCanSetParameter(): void
    {
        $this->request->setParameter('name', 'john');
        $this->assertSame('john', $this->request->getParameter('name'));
        $this->assertNull($this->request->getParameterList('name'));

        $this->request->setParameter('ids', ['1', '2']);
        $expected = ['1', '2'];
        $actual = $this->request->getParameterList('ids');
        $this->assertSameSize($expected, $actual);
        $this->assertSame($expected[0], $actual[0]);
        $this->assertSame($expected[1], $actual[1]);
        $this->assertNull($this->request->getParameter('ids'));
    }

    /**
     * @covers ::setUri
     * @covers ::getUri
     * @uses Covaleski\Framework\Web\Uri::fromString
     * @uses Covaleski\Framework\Web\Uri::__construct
     */
    public function testCanSetUri(): void
    {
        $uri = Uri::fromString('http://example.com');
        $this->request->setUri($uri);
        $this->assertSame($uri, $this->request->getUri());
    }

    /**
     * @covers ::setParameter
     */
    public function testMustSetParameterListWithLists(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->request->setParameter('ids', ['foo', 'bar' => 'baz']);
    }

    /**
     * @covers ::setParameter
     */
    public function testMustSetParameterListWithStringArrays(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->request->setParameter('ids', [1, 2, 3]);
    }
}
