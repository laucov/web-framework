<?php

namespace Covaleski\Framework\Http;

/**
 * Stores information about an HTTP request.
 */
interface ResponseInterface extends MessageInterface
{
    /**
     * Get the response status code.
     */
    public function getStatusCode(): int;

    /**
     * Get the response status text.
     */
    public function getStatusText(): string;
}
