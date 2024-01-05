<?php

namespace Laucov\WebFramework\Http;

use Laucov\WebFramework\Data\ArrayReader;
use Laucov\WebFramework\Web\Uri;

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
     * Get the request parameters.
     */
    public function getParameters(): ArrayReader;

    /**
     * Get the request POST variables.
     */
    public function getPostVariables(): ArrayReader;

    /**
     * Get the URI object.
     */
    public function getUri(): Uri;
}
