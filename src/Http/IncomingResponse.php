<?php

namespace Laucov\WebFramework\Http;

use Laucov\WebFramework\Http\Traits\ResponseTrait;

/**
 * Stores information about an incoming response.
 */
class IncomingResponse extends AbstractIncomingMessage implements
    ResponseInterface
{
    use ResponseTrait;

    /**
     * Create the incoming response instance.
     */
    public function __construct(
        mixed $content,
        array $headers,
        int $status_code,
        string $status_text,
    ) {
        $this->statusCode = $status_code;
        $this->statusText = $status_text;
        parent::__construct($content, $headers);
    }
}
