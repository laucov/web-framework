<?php

namespace Covaleski\Framework\Data;

/**
 * Controls the contents of an array.
 */
class ArrayBuilder
{
    /**
     * Stored array.
     */
    protected array $array;

    /**
     * Create the array builder instance.
     */
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * Get the array.
     */
    public function getArray(): array
    {
        return $this->array;
    }

    /**
     * Get a value.
     */
    public function getValue(
        int|string|array $keys,
        mixed $default_value = null,
    ): mixed {
        // Resolve single key.
        if (!is_array($keys)) {
            return $this->array[$keys] ?? $default_value;
        }

        // Check keys.
        if (!$this->validateKeys($keys)) {
            $message = 'Array keys must be strings or integers.';
            throw new \InvalidArgumentException($message);
        }

        // Get the last key.
        $last_key = array_pop($keys);

        // Find intermediary keys.
        $array = &$this->array;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) {
                return $default_value;
            }
            $array = &$array[$key];
            if (!is_array($array)) {
                return $default_value;
            }
        }

        return $array[$last_key] ?? $default_value;
    }

    // /**
    //  * Set a value.
    //  */
    // public function setValue(): void
    // {}

    /**
     * Validate an array of array keys.
     */
    protected function validateKeys(array $keys): bool
    {
        // Check array size.
        if (count($keys) < 1) {
            return false;
        }

        // Check each key.
        foreach ($keys as $key) {
            if (!is_int($key) && !is_string($key)) {
                return false;
            }
        }

        return true;
    }
}
