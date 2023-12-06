<?php

namespace Covaleski\Framework\HTTP;

/**
 * Stores information about an HTTP request.
 */
interface RequestInterface extends MessageInterface
{
    /**
     * Get the request method.
     */
    public function getMethod(): string;
}