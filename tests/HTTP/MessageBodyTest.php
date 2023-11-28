<?php

declare(strict_types=1);

namespace Tests\CLI;

use Covaleski\Framework\HTTP\MessageBody;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\HTTP\MessageBody
 */
final class MessageBodyTest extends TestCase
{
    private MessageBody $body;

    protected function setUp(): void
    {
        $body = 'The quick brown fox jumps over the lazy dog.';
        $this->body = new MessageBody($body);
    }

    /**
     * @covers ::__construct
     */
    protected function testMustInstantiateWithStringOrResource(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new MessageBody(['the', 'quick', 'brown', 'fox']);
    }
}
