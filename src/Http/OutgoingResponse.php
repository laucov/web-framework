<?php

namespace Covaleski\Framework\Http;

use Covaleski\Framework\Http\Traits\ResponseTrait;

/**
 * Stores information about an outgoing response.
 */
class OutgoingResponse extends AbstractOutgoingMessage implements
    ResponseInterface
{
    use ResponseTrait;

    /**
     * Set status code and text.
     */
    public function setStatus(int $code, string $text): void
    {
        $this->statusCode = $code;
        $this->statusText = $text;
    }
}
