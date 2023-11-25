<?php

declare(strict_types=1);

use Covaleski\Framework\CLI\AbstractRequest;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\CLI\AbstractRequest
 */
final class AbstractRequestTest extends TestCase
{
    private AbstractRequest $request;

    public function setUp(): void
    {
        $this->request = $this->getMockForAbstractClass(AbstractRequest::class);
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
