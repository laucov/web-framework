<?php

declare(strict_types=1);

namespace Tests\Cli;

use Laucov\WebFramework\Cli\AbstractCommand;
use Laucov\WebFramework\Cli\OutgoingRequest;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Cli\AbstractCommand
 */
final class AbstractCommandTest extends TestCase
{
    protected AbstractCommand $command;

    protected function setUp(): void
    {
        $request = new OutgoingRequest();
        $request->setCommand('do-something');

        $this->command = $this->getMockForAbstractClass(
            AbstractCommand::class,
            ['request' => $request],
        );
    }

    /**
     * @covers ::run
     * @covers ::__construct
     * @uses Laucov\WebFramework\Cli\OutgoingRequest::setCommand
     */
    public function testCanRun(): void
    {
        $this->assertNull($this->command->run());
    }
}
