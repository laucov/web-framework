<?php

declare(strict_types=1);

namespace Tests\Http;

use Covaleski\Framework\Data\ArrayBuilder;
use Covaleski\Framework\Http\Traits\RequestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\Http\Traits\RequestTrait
 */
class RequestTraitTest extends TestCase
{
    /**
     * @var RequestTrait
     */
    protected object $request;

    protected function setUp(): void
    {
        $this->request = $this->getMockForTrait(RequestTrait::class);
    }

    /**
     * @covers ::getMethod
     */
    public function testCanGetMethod(): void
    {
        $this->assertSame('GET', $this->request->getMethod());
    }

    /**
     * @coversNothing
     */
    public function testParametersMustBeInitialized(): void
    {
        set_error_handler(static function ($code, $message) {
            throw new \Exception($message, $code);
        }, E_ALL);
        $this->expectException(\Error::class);
        $this->request->parameters;
        restore_error_handler();
    }

    // /**
    //  * @covers ::getPostVariables
    //  */
    // public function testCanGetPostVariables(): void
    // {
    //     // @todo Get array builder for POST variables.
    // }

    /**
     * @covers ::getUri
     */
    public function testUriMustBeInitialized(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->request->getUri();
    }
}
