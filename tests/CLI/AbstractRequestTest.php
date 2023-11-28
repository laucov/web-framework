<?php

declare(strict_types=1);

namespace Tests\CLI;

use Covaleski\Framework\CLI\AbstractRequest;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\CLI\AbstractRequest
 */
final class AbstractRequestTest extends TestCase
{
    private AbstractRequest $request;

    protected function setUp(): void
    {
        $class_name = AbstractRequest::class;
        $this->request = $this->getMockForAbstractClass($class_name);
    }

    /**
     * @covers ::getArguments
     */
    public function testCanGetArguments(): void
    {
        $this->assertIsArray($this->request->getArguments());
    }

    /**
     * @covers ::getCommand
     */
    public function testCanGetCommand(): void
    {
        $this->assertNull($this->request->getCommand());
    }
}
