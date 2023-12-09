<?php

namespace Covaleski\Framework\Cli;

/**
 * Stores a command's information and procedures.
 */
abstract class AbstractCommand
{
    /**
     * Executes the command procedures.
     */
    abstract public function run(): void;

    /**
     * Create the command instance.
     */
    public function __construct(
        /**
         * Request being handled.
         */
        protected AbstractRequest $request,
    ) {
    }
}
