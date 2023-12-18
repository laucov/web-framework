<?php

declare(strict_types=1);

namespace Tests\Http;

use Covaleski\Framework\Data\ArrayBuilder;
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
     * @covers ::__construct
     * @covers ::getParameters
     * @uses Covaleski\Framework\Data\ArrayBuilder::__construct
     * @uses Covaleski\Framework\Http\OutgoingRequest::__construct
     */
    public function testCanGetParameters(): void
    {
        $parameters = $this->request->getParameters();
        $this->assertInstanceOf(ArrayBuilder::class, $parameters);
    }

    /**
     * @covers ::__construct
     * @covers ::getPostVariables
     * @uses Covaleski\Framework\Data\ArrayBuilder::__construct
     * @uses Covaleski\Framework\Http\OutgoingRequest::__construct
     */
    public function testCanGetPostVariables(): void
    {
        $variables = $this->request->getPostVariables();
        $this->assertInstanceOf(ArrayBuilder::class, $variables);
    }

    /**
     * @covers ::getMethod
     * @covers ::setMethod
     * @uses Covaleski\Framework\Data\ArrayBuilder::__construct
     * @uses Covaleski\Framework\Http\OutgoingRequest::__construct
     */
    public function testCanSetMethod(): void
    {
        $this->assertSame(
            $this->request,
            $this->request->setMethod('PUT'),
        );
        $this->assertSame('PUT', $this->request->getMethod());
        $this->request->setMethod('get');
        $this->assertSame('GET', $this->request->getMethod());
        $this->request->setMethod('PaTcH');
        $this->assertSame('PATCH', $this->request->getMethod());
    }

    /**
     * @covers ::getUri
     * @covers ::setUri
     * @uses Covaleski\Framework\Data\ArrayBuilder::__construct
     * @uses Covaleski\Framework\Http\OutgoingRequest::__construct
     * @uses Covaleski\Framework\Web\Uri::__construct
     * @uses Covaleski\Framework\Web\Uri::fromString
     */
    public function testCanSetUri(): void
    {
        $uri = Uri::fromString('http://example.com');
        $this->assertSame(
            $this->request,
            $this->request->setUri($uri),
        );
        $this->assertSame($uri, $this->request->getUri());
    }
}
