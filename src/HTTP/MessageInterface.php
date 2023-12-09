<?php

namespace Covaleski\Framework\HTTP;

use Covaleski\Framework\Files\StringSource;

/**
 * Stores information about an HTTP message.
 */
interface MessageInterface
{
    /**
     * Supported protocol versions.
     */
    const PROTOCOL_VERSIONS = ['1.0', '1.1', '2', '3'];

    /**
     * Get the message body.
     */
    public function getBody(): null|StringSource;

    /**
     * Get a message header.
     */
    public function getHeader(string $name): null|string;

    /**
     * Get a message header.
     * 
     * @return string[]
     */
    public function getHeaderAsList(string $name): null|array;

    /**
     * Get the HTTP protocol version.
     */
    public function getProtocolVersion(): null|string;
}
