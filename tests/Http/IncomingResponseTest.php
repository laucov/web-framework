<?php

declare(strict_types=1);

namespace Tests\Http;

use Covaleski\Framework\Http\IncomingResponse;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\Http\IncomingResponse
 */
class IncomingResponseTest extends TestCase
{
    /**
     * @covers ::__construct
     * @uses Covaleski\Framework\Files\StringSource::__construct
     * @uses Covaleski\Framework\Files\StringSource::read
     * @uses Covaleski\Framework\Http\AbstractIncomingMessage::__construct
     * @uses Covaleski\Framework\Http\AbstractMessage::getBody
     * @uses Covaleski\Framework\Http\AbstractMessage::getHeader
     * @uses Covaleski\Framework\Http\Traits\ResponseTrait::getStatusCode
     * @uses Covaleski\Framework\Http\Traits\ResponseTrait::getStatusText
     */
    public function testCanInstantiate(): void
    {
        $response = new IncomingResponse(
            content: 'Some message.',
            headers: [
                'Authorization' => 'Basic user:password',
            ],
            status_code: 401,
            status_text: 'Unauthorized',
        );
        $this->assertSame('Some message.', $response->getBody()->read(13));
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Unauthorized', $response->getStatusText());
        $header = $response->getHeader('Authorization');
        $this->assertSame('Basic user:password', $header);
    }
}
