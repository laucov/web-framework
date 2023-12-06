<?php

declare(strict_types=1);

namespace Tests\HTTP;

use Covaleski\Framework\HTTP\Traits\ResponseTrait;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\HTTP\Traits\ResponseTrait
 */
class ResponseTraitTest extends TestCase
{
    /**
     * @covers ::getStatusCode
     * @covers ::getStatusText
     */
    public function testCanGetStatus(): void
    {
        /** @var ResponseTrait */
        $response = $this->getMockForTrait(ResponseTrait::class);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getStatusText());
    }
}
