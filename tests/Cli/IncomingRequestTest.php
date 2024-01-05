<?php

declare(strict_types=1);

namespace Tests\Cli;

use Laucov\WebFramework\Cli\IncomingRequest;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Cli\IncomingRequest
 */
final class IncomingRequestTest extends TestCase
{
    private IncomingRequest $requestA;
    private IncomingRequest $requestB;
    private IncomingRequest $requestC;
    private IncomingRequest $requestD;
    private IncomingRequest $requestE;

    protected function setUp(): void
    {
        // Create with filename only.
        $arguments = [];
        $this->requestA = new IncomingRequest($arguments);
        // Create with filename and command.
        $arguments = ['script.php'];
        $this->requestB = new IncomingRequest($arguments);
        // Create with filename, command and 1 argument.
        $arguments = ['script.php', 'foo:bar'];
        $this->requestC = new IncomingRequest($arguments);
        // Create with filename, command and 2 arguments.
        $arguments = ['script.php', 'foo:bar', 'arg_1'];
        $this->requestD = new IncomingRequest($arguments);
        // Create with filename, command and 2 arguments.
        $arguments = ['script.php', 'foo:bar', 'arg_1', 'arg_2'];
        $this->requestE = new IncomingRequest($arguments);
    }

    /**
     * @covers ::__construct
     */
    public function testArgumentsMustBeStrings(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new IncomingRequest(['script.php', 123, [], 'argument']);
    }

    /**
     * @covers ::getArguments
     * @uses Laucov\WebFramework\Cli\IncomingRequest::__construct
     */
    public function testCanGetArguments(): void
    {
        $this->assertSame(count($this->requestA->getArguments()), 0);
        $this->assertSame(count($this->requestB->getArguments()), 0);
        $this->assertSame(count($this->requestC->getArguments()), 0);
        $this->assertSame(count($this->requestD->getArguments()), 1);
        $this->assertSame(count($this->requestE->getArguments()), 2);
    }

    /**
     * @covers ::getCommand
     * @uses Laucov\WebFramework\Cli\IncomingRequest::__construct
     */
    public function testCanGetCommand(): void
    {
        $this->assertNull($this->requestA->getCommand());
        $this->assertNull($this->requestB->getCommand());
        $this->assertIsString($this->requestC->getCommand());
        $this->assertIsString($this->requestD->getCommand());
        $this->assertIsString($this->requestE->getCommand());
    }

    /**
     * @covers ::getFilename
     * @uses Laucov\WebFramework\Cli\IncomingRequest::__construct
     */
    public function testCanGetFilename(): void
    {
        $this->assertNull($this->requestA->getFilename());
        $this->assertIsString($this->requestB->getFilename());
        $this->assertIsString($this->requestC->getFilename());
        $this->assertIsString($this->requestD->getFilename());
        $this->assertIsString($this->requestE->getFilename());
    }
}
