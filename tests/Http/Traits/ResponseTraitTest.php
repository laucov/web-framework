<?php

declare(strict_types=1);

namespace Tests\Http;

use Laucov\WebFramework\Http\Traits\ResponseTrait;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Http\Traits\ResponseTrait
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
