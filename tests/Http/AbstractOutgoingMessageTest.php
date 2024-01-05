<?php

declare(strict_types=1);

namespace Tests\Http;

use Laucov\WebFramework\Files\StringSource;
use Laucov\WebFramework\Http\AbstractOutgoingMessage;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Http\AbstractOutgoingMessage
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
     * @covers ::addHeader
     * @covers ::getHeader
     * @covers ::getHeaderAsList
     * @uses Laucov\WebFramework\Http\AbstractOutgoingMessage::setHeader
     */
    public function testCanAddHeaders(): void
    {
        $this->message->addHeader('Cache-Control', 'must-understand');
        $this->message->addHeader('Cache-Control', 'no-store');

        $line = $this->message->getHeader('Cache-Control');
        $this->assertSame('must-understand, no-store', $line);

        $list = $this->message->getHeaderAsList('Cache-Control');
        $this->assertCount(2, $list);
        $this->assertContains('must-understand', $list);
        $this->assertContains('no-store', $list);
    }

    /**
     * @covers ::getBody
     * @covers ::setBody
     * @uses Laucov\WebFramework\Files\StringSource::__construct
     * @uses Laucov\WebFramework\Files\StringSource::read
     */
    public function testCanSetBody(): void
    {
        $this->message->setBody('Lorem ipsum');
        /** @var StringSource */
        $body = $this->message->getBody();
        $this->assertInstanceOf(StringSource::class, $body);
        $this->assertSame('Lorem ipsum', $body->read(11));
    }

    /**
     * @covers ::setHeader
     * @uses Laucov\WebFramework\Http\AbstractMessage::getHeader
     */
    public function testCanSetHeader(): void
    {
        $this->message->setHeader('Content-Length', '10');
        $this->assertSame('10', $this->message->getHeader('Content-Length'));
    }

    /**
     * @covers ::setProtocolVersion
     * @uses Laucov\WebFramework\Http\AbstractMessage::getProtocolVersion
     */
    public function testCanSetProtocolVersion(): void
    {
        $this->message->setProtocolVersion('1.1');
        $this->assertSame('1.1', $this->message->getProtocolVersion());

        $this->expectException(\InvalidArgumentException::class);
        $this->message->setProtocolVersion('1.9');
    }

    /**
     * @covers ::addHeader
     * @covers ::setHeader
     * @uses Laucov\WebFramework\Http\AbstractMessage::getHeader
     * @uses Laucov\WebFramework\Http\AbstractMessage::getHeaderAsList
     */
    public function testFiltersValues(): void
    {
        $this->message->setHeader('Content-Length', " 20 \n\n   \t");
        $this->assertSame('20', $this->message->getHeader('Content-Length'));

        $this->message->addHeader('Cache-Control', "\n\n\n\r must-understand");
        $this->message->addHeader('Cache-Control', "   no-store ");

        $line = $this->message->getHeader('Cache-Control');
        $this->assertSame('must-understand, no-store', $line);

        $list = $this->message->getHeaderAsList('Cache-Control');
        $this->assertCount(2, $list);
        $this->assertContains('must-understand', $list);
        $this->assertContains('no-store', $list);
    }
}
