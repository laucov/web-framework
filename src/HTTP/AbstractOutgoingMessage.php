<?php

namespace Covaleski\Framework\HTTP;

use Covaleski\Framework\HTTP\AbstractMessage;
use Covaleski\Framework\Files\StringSource;

/**
 * Stores information about an HTTP outgoing message.
 */
abstract class AbstractOutgoingMessage extends AbstractMessage
{
    /**
     * Set the message body.
     * 
     * @param string|resource $content
     */
    public function setBody(mixed $content): static
    {
        $this->body = new StringSource($content);
        return $this;
    }

    // /**
    //  * Set a message header.
    //  */
    // public function setHeader(string $name, string $value): static
    // {
    //     return $this;
    // }
}
