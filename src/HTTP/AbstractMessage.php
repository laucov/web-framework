<?php

namespace Covaleski\Framework\HTTP;

use Covaleski\Framework\Files\StringSource;

/**
 * Stores information about an HTTP message.
 */
abstract class AbstractMessage implements MessageInterface
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
     * Get the message body.
     */
    public function getBody(): ?StringSource
    {
        return $this->body ?? null;
    }

    /**
     * Get a header value.
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Get a header list of values.
     * 
     * @return string[]
     */
    public function getHeaderAsList(string $name): ?array
    {
        $header = $this->getHeader($name);
        if ($header === null) {
            return null;
        }

        $values = explode(',', $this->headers[$name]);
        return array_map('trim', $values);
    }
}
