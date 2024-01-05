<?php

declare(strict_types=1);

namespace Tests\Cli;

use Laucov\WebFramework\Cli\OutgoingRequest;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Cli\OutgoingRequest
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
     * @uses Laucov\WebFramework\Cli\AbstractRequest::getArguments
     */
    public function testCanSetArguments(): void
    {
        $input = ['arg1', 'arg2', 'arg3'];
        $this->assertSame(
            $this->request,
            $this->request->setArguments($input),
        );

        $output = $this->request->getArguments();
        $this->assertSameSize($input, $output);
        foreach ($output as $i => $argument) {
            $this->assertSame($input[$i], $output[$i]);
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->request->setArguments(['arg1', 0, true]);
    }

    /**
     * @covers ::setCommand
     * @uses Laucov\WebFramework\Cli\AbstractRequest::getCommand
     */
    public function testCanSetCommand(): void
    {
        $this->assertSame(
            $this->request,
            $this->request->setCommand('do-something'),
        );
        $this->assertSame('do-something', $this->request->getCommand());
    }
}
