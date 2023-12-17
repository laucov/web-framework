<?php

declare(strict_types=1);

namespace Tests\Http;

use Covaleski\Framework\Data\ArrayBuilder;
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
     * @covers ::getParameters
     */
    public function testParametersMustBeInitialized(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->request->getParameters();
    }

    /**
     * @covers ::getPostVariables
     */
    public function testCanGetPostVariables(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->request->getPostVariables();
    }

    /**
     * @covers ::getUri
     */
    public function testUriMustBeInitialized(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->request->getUri();
    }
}
