<?php

namespace Covaleski\Framework\Http;

use Covaleski\Framework\Files\StringSource;

/**
 * Stores information about an HTTP incoming message.
 */
class AbstractIncomingMessage extends AbstractMessage
{
    /**
     * Create the incoming message instance.
     */
    public function __construct(mixed $content, array $headers)
    {
        // Set body.
        $this->body = new StringSource($content);

        // Set headers.
        foreach ($headers as $name => $value) {
            if (!is_string($name) || !is_string($value)) {
                $message = 'Header name and value must both be strings.';
                throw new \InvalidArgumentException($message);
            }
            $this->headers[$name] = $value;
        }
    }
}
