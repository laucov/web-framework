<?php

declare(strict_types=1);

namespace Tests\Cli;

use Covaleski\Framework\Cli\AbstractCommand;
use Covaleski\Framework\Cli\OutgoingRequest;
use Covaleski\Framework\Cli\Router;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\Cli\Router
 */
final class RouterTest extends TestCase
{
    protected Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    /**
     * @covers ::addCommand
     */
    public function testCanAddCommand(): void
    {
        $this->router->addCommand('valid-class', AbstractCommand::class);
        $this->expectException(\InvalidArgumentException::class);
        $this->router->addCommand('invalid-class', \stdClass::class);
    }

    /**
     * @covers ::route
     * @covers ::getCommand
     * @uses Covaleski\Framework\Cli\AbstractRequest::getCommand
     * @uses Covaleski\Framework\Cli\AbstractCommand::__construct
     * @uses Covaleski\Framework\Cli\OutgoingRequest::setArguments
     * @uses Covaleski\Framework\Cli\OutgoingRequest::setCommand
     * @uses Covaleski\Framework\Cli\Router::addCommand
     */
    public function testCanRoute(): void
    {
        // Get a request instance.
        $request = new OutgoingRequest();

        // Add example command.
        $mock = $this
            ->getMockBuilder(AbstractCommand::class)
            ->setConstructorArgs(['request' => $request])
            ->setMockClassName('RouterCommandTest')
            ->getMockForAbstractClass();
        $this->router->addCommand('test-the-router', $mock::class);

        // Route with valid command.
        $request->setCommand('test-the-router');
        $request->setArguments([]);
        $command = $this->router->route($request);
        $this->assertInstanceOf($mock::class, $command);

        // Route with invalid command.
        $request->setCommand('inexistent-command');
        $this->assertNull($this->router->route($request));

        // Route without command.
        $this->expectException(\InvalidArgumentException::class);
        $this->router->route(new OutgoingRequest());
    }
}
