<?php

declare(strict_types=1);

use Covaleski\Framework\CLI\Command;
use Covaleski\Framework\CLI\OutgoingRequest;
use Covaleski\Framework\CLI\Router;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\CLI\Router
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
        $this->router->addCommand('valid-class', Command::class);
        $this->expectException(\InvalidArgumentException::class);
        $this->router->addCommand('invalid-class', stdClass::class);
    }

    /**
     * @covers ::route
     * @covers ::getCommand
     * @uses Covaleski\Framework\CLI\AbstractRequest::getCommand
     * @uses Covaleski\Framework\CLI\OutgoingRequest::setArguments
     * @uses Covaleski\Framework\CLI\OutgoingRequest::setCommand
     * @uses Covaleski\Framework\CLI\Router::addCommand
     */
    public function testCanRoute(): void
    {
        // Add example command.
        $mock = $this
            ->getMockBuilder(Command::class)
            ->setMockClassName('RouterCommandTest')
            ->getMockForAbstractClass();
        $this->router->addCommand('test-the-router', $mock::class);

        // Get a request instance.
        $request = new OutgoingRequest();

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
