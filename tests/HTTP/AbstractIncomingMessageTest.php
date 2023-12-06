<?php

declare(strict_types=1);

namespace Tests\HTTP;

use Covaleski\Framework\HTTP\AbstractIncomingMessage;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\HTTP\AbstractIncomingMessage
 */
class AbstractIncomingMessageTest extends TestCase
{
    /**
     * @covers ::__construct
     * @uses Covaleski\Framework\Files\StringSource::__construct
     * @uses Covaleski\Framework\Files\StringSource::read
     * @uses Covaleski\Framework\HTTP\AbstractMessage::getBody
     * @uses Covaleski\Framework\HTTP\AbstractMessage::getHeader
     * @uses Covaleski\Framework\HTTP\AbstractMessage::getHeaderAsList
     */
    public function testCanInstantiate(): void
    {
        // Create instance.
        $message = $this->getInstance([
            'content' => 'The quick brown fox jumps over the lazy dog.',
            'headers' => [
                'Cache-Control' => 'must-understand, no-store',
                'Content-Length' => '44',
            ],
        ]);

        // Check body.
        /** @var \Covaleski\Framework\Files\StringSource */
        $body = $message->getBody();
        $this->assertNotNull($body);
        $this->assertSame('The quick', $body->read(9));

        // Check headers.
        $this->assertSame('44', $message->getHeader('Content-Length'));
        $list = $message->getHeaderAsList('Cache-Control');
        $this->assertCount(2, $list);
        $this->assertContains('must-understand', $list);
        $this->assertContains('no-store', $list);
    }

    /**
     * @covers ::__construct
     * @uses Covaleski\Framework\Files\StringSource::__construct
     */
    public function testMustPassStringHeaders(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->getInstance([
            'content' => '',
            'headers' => [
                'Cache-Control' => ['must-understand', 'no-store'],
                'Content-Length' => 44,
            ],
        ]);
    }

    /**
     * Get a mock for `AbstractIncomingMessage`.
     */
    protected function getInstance(array $arguments): AbstractIncomingMessage
    {
        $class_name = AbstractIncomingMessage::class;
        return $this->getMockForAbstractClass($class_name, $arguments);
    }
}
