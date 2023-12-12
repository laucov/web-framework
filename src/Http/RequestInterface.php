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
     * Get the URI object.
     */
    public function getUri(): Uri;
}
