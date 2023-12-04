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
    // protected AbstractIncomingMessage $message;

    // public function setUp(): void
    // {
    //     $class_name = AbstractIncomingMessage::class;
    //     $this->message = $this->getMockForAbstractClass($class_name);
    // }

    /**
     * @covers ::__construct
     * @uses Covaleski\Framework\Files\StringSource::__construct
     * @uses Covaleski\Framework\Files\StringSource::read
     * @uses Covaleski\Framework\HTTP\AbstractMessage::getBody
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

        // Check values.
        /** @var \Covaleski\Framework\Files\StringSource */
        $body = $message->getBody();
        $this->assertNotNull($body);
        $this->assertSame('The quick', $body->read(9));
    }

    protected function getInstance(array $arguments): AbstractIncomingMessage
    {
        $class_name = AbstractIncomingMessage::class;
        return $this->getMockForAbstractClass($class_name, $arguments);
    }
}
