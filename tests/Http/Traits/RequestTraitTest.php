<?php

declare(strict_types=1);

namespace Tests\Http;

use Covaleski\Framework\Http\Traits\RequestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\Http\Traits\RequestTrait
 */
class RequestTraitTest extends TestCase
{
    /**
     * @var RequestTrait
     */
    protected object $request;

    protected function setUp(): void
    {
        $this->request = $this->getMockForTrait(RequestTrait::class);
    }

    /**
     * @covers ::getMethod
     */
    public function testCanGetMethod(): void
    {
        $this->assertSame('GET', $this->request->getMethod());
    }

    /**
     * @covers ::getParameter
     */
    public function testCanGetParameter(): void
    {
        $this->assertNull($this->request->getParameter('foobar'));
    }

    /**
     * @covers ::getParameterList
     */
    public function testCanGetParameterList(): void
    {
        $this->assertNull($this->request->getParameterList('foobars'));
    }

    /**
     * @covers ::getUri
     */
    public function testCanGetUri(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->request->getUri();
    }
}
