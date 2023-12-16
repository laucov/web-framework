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
     * Parsed URI parameters.
     */
    public readonly ArrayBuilder $parameters;

    /**
     * HTTP method.
     */
    protected string $method = 'GET';

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
