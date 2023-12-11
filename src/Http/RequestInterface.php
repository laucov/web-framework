<?php

namespace Covaleski\Framework\Http;

use Covaleski\Framework\Web\Uri;

/**
 * Stores information about an HTTP request.
 */
interface RequestInterface extends MessageInterface
{
    /**
     * Get the request method.
     */
    public function getMethod(): string;

    /**
     * Get a single URL parameter.
     */
    public function getParameter(string $name): null|string;

    /**
     * Get a list of URL parameters.
     * 
     * @var null|string[]
     */
    public function getParameterList(string $name): null|array;

    /**
     * Get the URI object.
     */
    public function getUri(): Uri;
}
