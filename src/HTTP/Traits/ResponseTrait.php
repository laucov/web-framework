<?php

namespace Covaleski\Framework\HTTP\Traits;

/**
 * Has properties and methods common to response objects.
 */
trait ResponseTrait
{
    /**
     * Status code.
     */
    protected int $statusCode = 200;

    /**
     * Status code.
     */
    protected string $statusText = 'OK';

    /**
     * Get the response status code.
     * 
     * Returns `0` if no code is set.
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