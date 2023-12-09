<?php

declare(strict_types=1);

namespace Tests\Cli;

use Covaleski\Framework\Cli\OutgoingRequest;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\Cli\OutgoingRequest
 */
final class OutgoingRequestTest extends TestCase
{
    protected OutgoingRequest $request;

    protected function setUp(): void
    {
        $this->request = new OutgoingRequest();
    }

    /**
     * @covers ::setArguments
     * @uses Covaleski\Framework\Cli\AbstractRequest::getArguments
     */
    public function testCanSetArguments(): void
    {
        $input = ['arg1', 'arg2', 'arg3'];
        $this->request->setArguments($input);

        $output = $this->request->getArguments();
        $this->assertSameSize($input, $output);
        foreach ($output as $i => $argument) {
            $this->assertSame($input[$i], $output[$i]);
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->request->setArguments(['arg1', 0, true]);
    }

    // public function testCanAddParameter(): void
    // {
    // }

    /**
     * @covers ::setCommand
     * @uses Covaleski\Framework\Cli\AbstractRequest::getCommand
     */
    public function testCanSetCommand(): void
    {
        $this->request->setCommand('do-something');
        $this->assertSame('do-something', $this->request->getCommand());
    }

    // public function testCanSetParameter(): void
    // {
    // }
}
