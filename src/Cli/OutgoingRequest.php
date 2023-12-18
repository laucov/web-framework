<?php

namespace Covaleski\Framework\Cli;

/**
 * Stores information about a CLI outgoing request.
 */
class OutgoingRequest extends AbstractRequest
{
    /**
     * Set the arguments.
     */
    public function setArguments(array $arguments): static
    {
        foreach ($arguments as $argument) {
            if (!is_string($argument)) {
                $message = 'All arguments must be of type string.';
                throw new \InvalidArgumentException($message);
            }
        }

        $this->arguments = $arguments;
        return $this;
    }

    /**
     * Set the command name.
     */
    public function setCommand(string $name): static
    {
        $this->command = $name;
        return $this;
    }
}
