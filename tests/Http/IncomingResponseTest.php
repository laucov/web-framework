<?php

declare(strict_types=1);

namespace Tests\Http;

use Laucov\WebFramework\Http\IncomingResponse;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Http\IncomingResponse
 */
class IncomingResponseTest extends TestCase
{
    /**
     * @covers ::__construct
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Files\StringSource::read
     * @uses Laucov\WebFramework\Http\AbstractIncomingMessage::__construct
     * @uses Laucov\WebFramework\Http\AbstractMessage::getBody
     * @uses Laucov\WebFramework\Http\AbstractMessage::getHeader
     * @uses Laucov\WebFramework\Http\Traits\ResponseTrait::getStatusCode
     * @uses Laucov\WebFramework\Http\Traits\ResponseTrait::getStatusText
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
