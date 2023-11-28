<?php

namespace Covaleski\Framework\HTTP;

/**
 * Stores information about an HTTP message.
 */
abstract class AbstractMessage
{
    /**
     * Stored message body.
     */
    protected MessageBody $body;

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
    public function getBody(): ?MessageBody
    {
        return $this->body ?? null;
    }
}
