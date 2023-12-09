<?php

namespace Covaleski\Framework\Cli;

/**
 * Stores information about a CLI incoming request.
 */
class IncomingRequest extends AbstractRequest
{
    /**
     * Script handling the request.
     */
    protected string $filename;

    /**
     * Create the incoming request instance.
     */
    public function __construct(array $php_arguments)
    {
        foreach ($php_arguments as $i => $argument) {
            // Check if the argument is a string.
            if (!is_string($argument)) {
                $message = 'All arguments must be of type string.';
                throw new \InvalidArgumentException($message);
            }
            // Parse argument.
            switch ($i) {
                case 0:
                    $this->filename = $argument;
                    break;
                case 1:
                    $this->command = $argument;
                    break;
                default:
                    $this->arguments[] = $argument;
            }
        }
    }

    /**
     * Get the script handling the request.
     */
    public function getFilename(): ?string
    {
        return $this->filename ?? null;
    }
}
