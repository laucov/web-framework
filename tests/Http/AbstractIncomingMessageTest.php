<?php

declare(strict_types=1);

namespace Tests\Http;

use Laucov\WebFramework\Http\AbstractIncomingMessage;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Http\AbstractIncomingMessage
 */
class AbstractIncomingMessageTest extends TestCase
{
    /**
     * @covers ::__construct
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Files\StringSource::read
     * @uses Laucov\WebFramework\Http\AbstractMessage::getBody
     * @uses Laucov\WebFramework\Http\AbstractMessage::getHeader
     * @uses Laucov\WebFramework\Http\AbstractMessage::getHeaderAsList
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
        /** @var \Laucov\WebFramework\Files\StringSource */
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
     * @uses Laucov\WebFramework\Files\StringSource::__construct
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
