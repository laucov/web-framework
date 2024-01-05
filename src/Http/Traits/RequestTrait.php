<?php

namespace Laucov\WebFramework\Http\Traits;

use Laucov\WebFramework\Data\ArrayReader;
use Laucov\WebFramework\Web\Uri;

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
