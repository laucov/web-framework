<?php

namespace Covaleski\Framework\CLI;

/**
 * Stores information about a CLI request.
 */
abstract class AbstractRequest
{
    /**
     * Current arguments.
     * 
     * @var string[]
     */
    protected array $arguments;

    /**
     * Current command.
     */
    protected string $command;

    /**
     * Get arguments.
     */
    public function getArguments(): array
    {
        return $this->arguments ?? [];
    }

    /**
     * Get the command.
     */
    public function getCommand(): ?string
    {
        return $this->command ?? null;
    }
}
