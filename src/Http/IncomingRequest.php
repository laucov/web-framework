<?php

namespace Laucov\WebFramework\Http;

use Laucov\WebFramework\Data\ArrayReader;
use Laucov\WebFramework\Http\Traits\RequestTrait;

/**
 * Stores information about an incoming request.
 */
class IncomingRequest extends AbstractIncomingMessage implements
    RequestInterface
{
    use RequestTrait;

    /**
     * Parsed URI parameters.
     */
    protected ArrayReader $parameters;

    /**
     * Parsed POST variables.
     */
    protected ArrayReader $postVariables;

    /**
     * Create the outgoing request instance.
     */
    public function __construct(
        mixed $content_or_post,
        array $headers,
        array $parameters,
    ) {
        // Set parameters.
        $this->parameters = new ArrayReader($parameters);

        // Set POST variables and run the parent's constructor.
        if (is_array($content_or_post)) {
            $this->postVariables = new ArrayReader($content_or_post);
            parent::__construct('', $headers);
        } else {
            $this->postVariables = new ArrayReader([]);
            parent::__construct($content_or_post, $headers);
        }
    }

    /**
     * Get the parameters.
     */
    public function getParameters(): ArrayReader
    {
        return $this->parameters;
    }

    /**
     * Get the POST variables.
     */
    public function getPostVariables(): ArrayReader
    {
        return $this->postVariables;
    }
}
