<?php

declare(strict_types=1);

namespace Tests\Http;

use Covaleski\Framework\Http\OutgoingResponse;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\Http\OutgoingResponse
 */
class OutgoingResponseTest extends TestCase
{
    protected OutgoingResponse $response;

    protected function setUp(): void
    {
        $this->response = new OutgoingResponse();
    }

    /**
     * @covers ::getStatusCode
     * @covers ::getStatusText
     * @covers ::setStatus
     */
    public function testCanSetStatus(): void
    {
        $this->assertSame(
            $this->response,
            $this->response->setStatus(201, 'Created'),
        );
        $this->assertSame(201, $this->response->getStatusCode());
        $this->assertSame('Created', $this->response->getStatusText());
    }
}
