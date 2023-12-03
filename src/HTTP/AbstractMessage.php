<?php

namespace Covaleski\Framework\HTTP;

use Covaleski\Framework\Files\StringSource;

/**
 * Stores information about an HTTP message.
 */
abstract class AbstractMessage
{
    /**
     * Stored message body.
     */
    protected StringSource $body;

    /**
     * Stored headers.
     * 
     * @var array<string, string>
     */
    protected array $headers;

    /**
     * Get a header value.
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Get the message body.
     */
    public function getBody(): ?StringSource
    {
        return $this->body ?? null;
    }
}
