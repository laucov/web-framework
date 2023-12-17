<?php

namespace Covaleski\Framework\Http\Traits;

use Covaleski\Framework\Data\ArrayBuilder;
use Covaleski\Framework\Web\Uri;

/**
 * Has properties and methods common to request objects.
 */
trait RequestTrait
{
    /**
     * HTTP method.
     */
    protected string $method = 'GET';

    /**
     * Parsed URI parameters.
     */
    protected null|ArrayBuilder $parameters = null;

    /**
     * Parsed POST variables.
     */
    protected null|ArrayBuilder $postVariables = null;

    /**
     * URI object.
     */
    protected null|Uri $uri = null;

    /**
     * Get the request method.
     * 
     * Always returns the method name in uppercase characters.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get the parameters.
     */
    public function getParameters(): ArrayBuilder
    {
        if ($this->parameters === null) {
            throw new \RuntimeException('Request parameters are not defined.');
        }

        return $this->parameters;
    }

    /**
     * Get the POST variables.
     */
    public function getPostVariables(): ArrayBuilder
    {
        if ($this->postVariables === null) {
            $message = 'Request POST variables are not defined.';
            throw new \RuntimeException($message);
        }

        return $this->postVariables;
    }

    /**
     * Get the URI object.
     */
    public function getUri(): Uri
    {
        if ($this->uri === null) {
            throw new \RuntimeException('Request URI is not defined.');
        }

        return $this->uri;
    }
}
