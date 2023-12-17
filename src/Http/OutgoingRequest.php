<?php

namespace Covaleski\Framework\Http;

use Covaleski\Framework\Data\ArrayBuilder;
use Covaleski\Framework\Http\Traits\RequestTrait;
use Covaleski\Framework\Web\Uri;

/**
 * Stores information about an outgoing request.
 */
class OutgoingRequest extends AbstractOutgoingMessage implements
    RequestInterface
{
    use RequestTrait;

    /**
     * Create the outgoing request instance.
     */
    public function __construct()
    {
        $this->parameters = new ArrayBuilder([]);
        $this->postVariables = new ArrayBuilder([]);
    }

    /**
     * Set the request method.
     */
    public function setMethod(string $method): void
    {
        $this->method = strtoupper($method);
    }

    /**
     * Set the request URI.
     */
    public function setUri(Uri $uri): void
    {
        $this->uri = $uri;
    }
}
