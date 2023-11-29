<?php

namespace Covaleski\Framework\Files;

/**
 * Provides type-agnostic read operations on strings and resources.
 */
class StringSource
{
    /**
     * Whether the stored source is a resource.
     */
    protected bool $isResource;

    /**
     * String offset - file pointer simulation.
     */
    protected int $offset;

    /**
     * Stored resource.
     * 
     * @var resource
     */
    protected mixed $resource;

    /**
     * Stored string.
     */
    protected string $string;

    /**
     * Create the string source instance.
     */
    public function __construct(mixed $content)
    {
        if (is_string($content)) {
            // Use string.
            $this->string = $content;
            $this->isResource = false;
            $this->offset = 0;
        } elseif (is_resource($content)) {
            // Use resource.
            $this->resource = $content;
            $this->isResource = true;
        } else {
            $message = 'Content must be a string or a resource.';
            throw new \InvalidArgumentException($message);
        }
    }

    /**
     * Get the source size in bytes.
     */
    public function getSize(): int
    {
        // Get the resource size.
        if ($this->isResource) {
            $stat = fstat($this->resource);
            if (!is_array($stat)) {
                $message = 'Could not collect the resource information.';
                throw new \RuntimeException($message);
            }
            return (int) $stat['size'];
        }

        // Get the string length.
        $length = strlen($this->string);

        return $length;
    }

    /**
     * Read an amount of bytes from the source.
     */
    public function read(int $length): string
    {
        // Get resource content.
        if ($this->isResource === true) {
            $content = fread($this->resource, $length);
            if (!is_string($content)) {
                $message = 'Could not read the resource.';
                throw new \RuntimeException($message);
            }
            return $content;
        }

        // Get string slice and move the offset.
        $string = substr($this->string, $this->offset, $length);
        $this->offset += strlen($string);

        return $string;
    }

    // /**
    //  * Move the content pointer to the designated position.
    //  */
    // public function seek(int $position): void
    // {
    //     // Move the resource file pointer.
    //     if ($this->isResource) {
    //         $content = fseek($this->resource, $position);
    //         if (!is_string($content)) {
    //             $message = 'Could not move the resource file pointer.';
    //             throw new \RuntimeException($message);
    //         }
    //         return;
    //     }

    //     // Set the string offset.
    //     $this->offset = $position;
    // }
}
