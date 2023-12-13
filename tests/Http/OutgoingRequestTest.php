<?php

declare(strict_types=1);

namespace Tests\Http;

use Covaleski\Framework\Http\OutgoingRequest;
use Covaleski\Framework\Web\Uri;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\Http\OutgoingRequest
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
     * @covers ::getUri
     * @covers ::setUri
     * @uses Covaleski\Framework\Web\Uri::__construct
     * @uses Covaleski\Framework\Web\Uri::fromString
     */
    public function testCanSetUri(): void
    {
        $uri = Uri::fromString('http://example.com');
        $this->request->setUri($uri);
        $this->assertSame($uri, $this->request->getUri());
    }
}
