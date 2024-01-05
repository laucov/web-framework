<?php

declare(strict_types=1);

namespace Tests\Http;

use Laucov\WebFramework\Data\ArrayBuilder;
use Laucov\WebFramework\Http\Traits\RequestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Http\Traits\RequestTrait
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
     * @covers ::getUri
     */
    public function testUriMustBeInitialized(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->request->getUri();
    }
}
