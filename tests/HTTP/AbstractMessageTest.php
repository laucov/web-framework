<?php

declare(strict_types=1);

namespace Tests\CLI;

use Covaleski\Framework\HTTP\AbstractMessage;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\HTTP\AbstractMessage
 */
final class AbstractMessageTest extends TestCase
{
    private AbstractMessage $request;

    protected function setUp(): void
    {
        $class_name = AbstractMessage::class;
        $this->request = $this->getMockForAbstractClass($class_name);
    }

    /**
     * @covers ::getHeader
     */
    public function testCanGetHeader(): void
    {
        $this->assertNull($this->request->getHeader('Content-Type'));
    }

    // /**
    //  * @covers ::getBody
    //  */
    // public function testCanGetBody(): void
    // {
    //     $this->assertNull($this->request->getBody());
    // }
}