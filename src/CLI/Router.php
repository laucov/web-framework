<?php

namespace Covaleski\Framework\CLI;

/**
 * Stores routes and provides `Command` instances.
 */
class Router
{
    /**
     * Stored command names.
     * 
     * @var array<string, class-string<Command>>
     */
    protected array $commands = [];

    /**
     * Add a command.
     */
    public function addCommand(string $name, string $class_string): void
    {
        if (!is_a($class_string, Command::class, true)) {
            $class_name = Command::class;
            $message = 'The command class must extend "' . $class_name . '".';
            throw new \InvalidArgumentException($message);
        }

        $this->commands[$name] = $class_string;
    }

    // /**
    //  * Retrieve a command based on the given request object.
    //  */
    // public function route(AbstractRequest $request): ?Command
    // {
    //     $command_name = $request->getCommand();
    //     if ($command_name === null) {
    //         return null;
    //     }

    //     $class_string = $this->getCommand($command_name);
    //     if ($class_string === null) {
    //         return null;
    //     }

    //     $command = new $class_string();
    //     return $command;
    // }

    // /**
    //  * Get the command class name stored under the given name.
    //  */
    // protected function getCommand(string $name): ?string
    // {
    //     return $this->commands[$name] ?? null;
    // }
}