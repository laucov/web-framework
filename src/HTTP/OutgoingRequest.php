<?php

namespace Covaleski\Framework\HTTP;

use Covaleski\Framework\HTTP\Traits\RequestTrait;

/**
 * Stores information about an outgoing request.
 */
class OutgoingRequest extends AbstractOutgoingMessage implements
    RequestInterface
{
    use RequestTrait;

    /**
     * Set the request method.
     */
    public function setMethod(string $method): void
    {
        $this->method = strtoupper($method);
    }
}
