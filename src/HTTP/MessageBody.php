<?php

namespace Covaleski\Framework\HTTP;

/**
 * Provides a type agnostic interface to a message body.
 * 
 * Allows to read both string and stream bodies.
 */
class MessageBody
{
    /**
     * Stored string or stream.
     * 
     * @var string|resource
     */
    protected mixed $content;

    /**
     * Create the message body instance.
     */
    public function __construct(mixed $content)
    {
        if (!is_string($content) && !is_resource($content)) {
            $message = 'A message body content must be a string or resource.';
            throw new \InvalidArgumentException($message);
        }

        $this->content = $content;
    }
}