<?php

namespace Covaleski\Framework\Http;

use Covaleski\Framework\Http\Traits\RequestTrait;
use Covaleski\Framework\Web\Uri;

/**
 * Stores information about an outgoing request.
 */
class OutgoingRequest extends AbstractOutgoingMessage implements
    RequestInterface
{
    use RequestTrait;

    /**
     * Set the request method.
     */
    public function setMethod(string $method): void
    {
        $this->method = strtoupper($method);
    }

    /**
     * Set a URI parameter.
     */
    public function setParameter(string $name, string|array $value): void
    {
        // Ensure parameter lists have only string elements.
        if (is_array($value)) {
            if (!array_is_list($value)) {
                $message = 'Non-list array given as parameter list.';
                throw new \InvalidArgumentException($message);
            }
            foreach ($value as $item) {
                if (!is_string($item)) {
                    $message = 'Parameter lists must only contain strings.';
                    throw new \InvalidArgumentException($message);
                }
            }
        }

        // Set parameter.
        $this->parameters[$name] = $value;
    }

    /**
     * Set the request URI.
     */
    public function setUri(Uri $uri): void
    {
        $this->uri = $uri;
    }
}
