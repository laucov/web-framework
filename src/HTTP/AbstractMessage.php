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
    protected null|StringSource $body = null;

    /**
     * Stored headers.
     * 
     * @var array<string, string>
     */
    protected array $headers = [];

    /**
     * HTTP protocol version.
     */
    protected null|string $protocolVersion = null;

    /**
     * Get the message body.
     */
    public function getBody(): null|StringSource
    {
        return $this->body;
    }

    /**
     * Get a header value.
     */
    public function getHeader(string $name): null|string
    {
        return array_key_exists($name, $this->headers)
            ? $this->headers[$name]
            : null;
    }

    /**
     * Get a header list of values.
     * 
     * @return string[]
     */
    public function getHeaderAsList(string $name): null|array
    {
        $header = $this->getHeader($name);
        if ($header === null) {
            return null;
        }

        $values = explode(',', $this->headers[$name]);
        return array_map('trim', $values);
    }

    /**
     * Get the HTTP protocol version.
     */
    public function getProtocolVersion(): null|string
    {
        return $this->protocolVersion;
    }
}
