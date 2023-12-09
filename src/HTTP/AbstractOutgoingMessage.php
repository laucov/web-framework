<?php

namespace Covaleski\Framework\HTTP;

use Covaleski\Framework\Files\StringSource;

/**
 * Stores information about an HTTP outgoing message.
 */
abstract class AbstractOutgoingMessage extends AbstractMessage
{
    /**
     * Add a message header.
     * 
     * If the header exists, appends the value, otherwise creates it.
     */
    public function addHeader(string $name, string $value): static
    {
        $list = $this->getHeaderAsList($name);

        if ($list === null) {
            return $this->setHeader($name, $value);
        }

        $list[] = trim($value);
        return $this->setHeader($name, implode(', ', $list));
    }
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

    /**
     * Set a message header.
     */
    public function setHeader(string $name, string $value): static
    {
        $this->headers[$name] = trim($value);
        return $this;
    }

    /**
     * Set the HTTP protocol version.
     */
    public function setProtocolVersion(null|string $version): static
    {
        if (!in_array($version, static::PROTOCOL_VERSIONS, true)) {
            $versions = implode(', ', static::PROTOCOL_VERSIONS);
            $message = 'Unknown HTTP version "%s". Supported values: %s.';
            throw new \InvalidArgumentException(
                sprintf($message, $version, $versions),
            );
        }
        $this->protocolVersion = $version;
        
        return $this;
    }
}
