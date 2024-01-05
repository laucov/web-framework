<?php

namespace Laucov\WebFramework\Http;

use Laucov\WebFramework\Data\ArrayBuilder;
use Laucov\WebFramework\Http\Traits\RequestTrait;
use Laucov\WebFramework\Web\Uri;

/**
 * Stores information about an outgoing request.
 */
class OutgoingRequest extends AbstractOutgoingMessage implements
    RequestInterface
{
    use RequestTrait;

    /**
     * Parsed URI parameters.
     */
    protected ArrayBuilder $parameters;

    /**
     * Parsed POST variables.
     */
    protected ArrayBuilder $postVariables;

    /**
     * Create the outgoing request instance.
     */
    public function __construct()
    {
        $this->parameters = new ArrayBuilder([]);
        $this->postVariables = new ArrayBuilder([]);
    }

    /**
     * Get the parameters.
     */
    public function getParameters(): ArrayBuilder
    {
        return $this->parameters;
    }

    /**
     * Get the POST variables.
     */
    public function getPostVariables(): ArrayBuilder
    {
        return $this->postVariables;
    }

    /**
     * Set the request method.
     */
    public function setMethod(string $method): static
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * Set the request URI.
     */
    public function setUri(Uri $uri): static
    {
        $this->uri = $uri;
        return $this;
    }
}
