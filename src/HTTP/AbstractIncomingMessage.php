<?php

namespace Covaleski\Framework\HTTP;

use Covaleski\Framework\Files\StringSource;

/**
 * Stores information about an HTTP incoming message.
 */
class AbstractIncomingMessage extends AbstractMessage
{
    public function __construct(mixed $content, array $headers)
    {
        $this->body = new StringSource($content);
    }
}
