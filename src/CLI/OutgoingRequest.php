<?php

namespace Covaleski\Framework\CLI;

/**
 * Stores information about a CLI outgoing request.
 */
class OutgoingRequest extends AbstractRequest
{
    /**
     * Set the arguments.
     */
    public function setArguments(array $arguments): void
    {
        foreach ($arguments as $argument) {
            if (!is_string($argument)) {
                $message = 'All arguments must be of type string.';
                throw new \InvalidArgumentException($message);
            }
        }

        $this->arguments = $arguments;
    }

    /**
     * Set the command name.
     */
    public function setCommand(string $name): void
    {
        $this->command = $name;
    }
}
