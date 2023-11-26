<?php

declare(strict_types=1);

use Covaleski\Framework\CLI\Command;
use Covaleski\Framework\CLI\OutgoingRequest;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\CLI\Command
 */
final class CommandTest extends TestCase
{
    protected Command $command;

    protected function setUp(): void
    {
        $request = new OutgoingRequest();
        $request->setCommand('do-something');

        $this->command = $this->getMockForAbstractClass(
            Command::class,
            ['request' => $request],
        );
    }

    /**
     * @covers ::run
     * @covers ::__construct
     * @uses Covaleski\Framework\CLI\OutgoingRequest::setCommand
     */
    public function testCanRun(): void
    {
        $this->assertNull($this->command->run());
    }
}
