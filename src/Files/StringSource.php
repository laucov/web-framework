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
            // @codeCoverageIgnoreStart
            if (!is_array($stat)) {
                $message = 'Could not collect the resource information.';
                throw new \RuntimeException($message);
            }
            $size = $stat['size'] ?? null;
            if (!is_int($size)) {
                $message = 'Could not get the resource size.';
                throw new \RuntimeException($message);
            }
            // @codeCoverageIgnoreEnd
            return $size;
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
        // Check position value.
        if ($length < 0) {
            $message = 'Length must be a positive number.';
            throw new \InvalidArgumentException($message);
        }

        // Get resource content.
        if ($this->isResource === true) {
            $content = fread($this->resource, $length);
            // @codeCoverageIgnoreStart
            if (!is_string($content)) {
                $message = 'Could not read the resource.';
                throw new \RuntimeException($message);
            }
            // @codeCoverageIgnoreEnd
            return $content;
        }

        // Get string slice and move the offset.
        $string = substr($this->string, $this->offset, $length);
        $this->offset += strlen($string);

        return $string;
    }

    /**
     * Move the content pointer to the designated position.
     */
    public function seek(int $position): void
    {
        // Check position value.
        if ($position < 0 || $position > $this->getSize()) {
            $message = 'Invalid position given.';
            throw new \InvalidArgumentException($message);
        }

        // Move the resource file pointer.
        if ($this->isResource) {
            $result = fseek($this->resource, $position);
            // @codeCoverageIgnoreStart
            if ($result !== 0) {
                $message = 'Could not move the resource file pointer.';
                throw new \RuntimeException($message);
            }
            // @codeCoverageIgnoreEnd
            return;
        }

        // Set the string offset.
        $this->offset = $position;
    }

    /**
     * Get the current content pointer position.
     */
    public function tell(): int
    {
        // Get the resource file pointer.
        if ($this->isResource) {
            $position = ftell($this->resource);
            // @codeCoverageIgnoreStart
            if (!is_int($position)) {
                $message = 'Could not get the resource file pointer position.';
                throw new \RuntimeException($message);
            }
            // @codeCoverageIgnoreEnd
            return $position;
        }

        // Set the string offset.
        return $this->offset;
    }
}
