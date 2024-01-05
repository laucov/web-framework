<?php

declare(strict_types=1);

namespace Tests\Cli;

use Laucov\WebFramework\Cli\AbstractCommand;
use Laucov\WebFramework\Cli\OutgoingRequest;
use Laucov\WebFramework\Cli\Router;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFramework\Cli\Router
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
        $this->assertSame(
            $this->router,
            $this->router->addCommand('valid-class', AbstractCommand::class),
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->router->addCommand('invalid-class', \stdClass::class);
    }

    /**
     * @covers ::route
     * @covers ::getCommand
     * @uses Laucov\WebFramework\Cli\AbstractRequest::getCommand
     * @uses Laucov\WebFramework\Cli\AbstractCommand::__construct
     * @uses Laucov\WebFramework\Cli\OutgoingRequest::setArguments
     * @uses Laucov\WebFramework\Cli\OutgoingRequest::setCommand
     * @uses Laucov\WebFramework\Cli\Router::addCommand
     */
    public function testCanRoute(): void
    {
        // Get a request instance.
        $request = new OutgoingRequest();

        // Create example command.
        $mock = $this
            ->getMockBuilder(AbstractCommand::class)
            ->setConstructorArgs(['request' => $request])
            ->setMockClassName('RouterCommandTest')
            ->getMockForAbstractClass();

        // Route with valid command.
        $request->setCommand('test-the-router');
        $request->setArguments([]);
        $command = $this->router
            ->addCommand('test-the-router', $mock::class)
            ->route($request);
        $this->assertInstanceOf($mock::class, $command);

        // Route with invalid command.
        $request->setCommand('inexistent-command');
        $this->assertNull($this->router->route($request));

        // Route without command.
        $this->expectException(\InvalidArgumentException::class);
        $this->router->route(new OutgoingRequest());
    }
}
