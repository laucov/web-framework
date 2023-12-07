<?php

namespace Covaleski\Framework\HTTP\Traits;

/**
 * Has properties and methods common to response objects.
 */
trait ResponseTrait
{
    /**
     * HTTP status code.
     */
    protected int $statusCode = 200;

    /**
     * HTTP status text.
     */
    protected string $statusText = 'OK';

    /**
     * Get the response status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the response status text.
     */
    public function getStatusText(): string
    {
        return $this->statusText;
    }
}
