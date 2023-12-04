<?php

declare(strict_types=1);

namespace Tests\HTTP;

use Covaleski\Framework\Files\StringSource;
use Covaleski\Framework\HTTP\AbstractOutgoingMessage;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\HTTP\AbstractOutgoingMessage
 */
class AbstractOutgoingMessageTest extends TestCase
{
    private AbstractOutgoingMessage $message;

    protected function setUp(): void
    {
        $class_name = AbstractOutgoingMessage::class;
        $this->message = $this->getMockForAbstractClass($class_name);
    }

    /**
     * @covers ::getBody
     * @covers ::setBody
     * @uses Covaleski\Framework\Files\StringSource::__construct
     * @uses Covaleski\Framework\Files\StringSource::read
     */
    public function testCanSetBody(): void
    {
        $this->message->setBody('Lorem ipsum');
        /** @var StringSource */
        $body = $this->message->getBody();
        $this->assertInstanceOf(StringSource::class, $body);
        $this->assertSame('Lorem ipsum', $body->read(11));
    }

    // /**
    //  * @covers ::setHeader
    //  */
    // public function testCanSetHeader(): void
    // {
    //     $this->message->setHeader('Content-Length', '10');
    //     $this->assertSame('10', $this->message->getHeader('Content-Length'));
    // }
}
