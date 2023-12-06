<?php

namespace Covaleski\Framework\HTTP;
use Covaleski\Framework\Files\StringSource;

/**
 * Stores information about an HTTP message.
 */
interface MessageInterface
{
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
     */
    public function getHeaderAsList(string $name): null|array;
}
