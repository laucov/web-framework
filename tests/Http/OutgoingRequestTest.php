<?php

declare(strict_types=1);

namespace Tests\Http;

use Laucov\WebFramework\Data\ArrayBuilder;
use Laucov\WebFramework\Http\OutgoingRequest;
use Laucov\WebFramework\Web\Uri;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Http\OutgoingRequest
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
     * @uses Laucov\WebFramework\Data\ArrayBuilder::__construct
     * @uses Laucov\WebFramework\Http\OutgoingRequest::__construct
     */
    public function testCanGetParameters(): void
    {
        $parameters = $this->request->getParameters();
        $this->assertInstanceOf(ArrayBuilder::class, $parameters);
    }

    /**
     * @covers ::__construct
     * @covers ::getPostVariables
     * @uses Laucov\WebFramework\Data\ArrayBuilder::__construct
     * @uses Laucov\WebFramework\Http\OutgoingRequest::__construct
     */
    public function testCanGetPostVariables(): void
    {
        $variables = $this->request->getPostVariables();
        $this->assertInstanceOf(ArrayBuilder::class, $variables);
    }

    /**
     * @covers ::getMethod
     * @covers ::setMethod
     * @uses Laucov\WebFramework\Data\ArrayBuilder::__construct
     * @uses Laucov\WebFramework\Http\OutgoingRequest::__construct
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
     * @uses Laucov\WebFramework\Data\ArrayBuilder::__construct
     * @uses Laucov\WebFramework\Http\OutgoingRequest::__construct
     * @uses Laucov\WebFramework\Web\Uri::__construct
     * @uses Laucov\WebFramework\Web\Uri::fromString
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
