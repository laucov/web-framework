<?php

declare(strict_types=1);

namespace Tests\HTTP;

use Covaleski\Framework\HTTP\AbstractMessage;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\HTTP\AbstractMessage
 */
final class AbstractMessageTest extends TestCase
{
    private AbstractMessage $message;

    protected function setUp(): void
    {
        $class_name = AbstractMessage::class;
        $this->message = $this->getMockForAbstractClass($class_name);
    }

    /**
     * @covers ::getHeader
     * @covers ::getHeaderAsList
     */
    public function testCanGetHeader(): void
    {
        $this->assertNull($this->message->getHeader('Content-Type'));
        $this->assertNull($this->message->getHeaderAsList('Cache-Control'));
    }

    /**
     * @covers ::getBody
     */
    public function testCanGetBody(): void
    {
        $this->assertNull($this->message->getBody());
    }
}