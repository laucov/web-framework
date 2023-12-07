<?php

declare(strict_types=1);

namespace Tests\HTTP;

use Covaleski\Framework\HTTP\Traits\RequestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\HTTP\Traits\RequestTrait
 */
class RequestTraitTest extends TestCase
{
    /**
     * @covers ::getMethod
     */
    public function testCanGetMethod(): void
    {
        /** @var RequestTrait */
        $response = $this->getMockForTrait(RequestTrait::class);
        $this->assertSame('GET', $response->getMethod());
    }
}
