<?php

namespace Covaleski\Framework\Data;

/**
 * Reads the contents of an array.
 */
class ArrayReader
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
        $keys = $this->validateKeys($keys);

        // Get the last key.
        $last_key = array_pop($keys);

        // Find intermediary keys.
        $array = &$this->array;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array) || !is_array($array[$key])) {
                return $default_value;
            }
            $array = &$array[$key];
        }

        return array_key_exists($last_key, $array)
            ? $array[$last_key]
            : $default_value;
    }

    /**
     * Check if a value exists.
     */
    public function hasValue(int|string|array $keys): bool
    {
        // Resolve single key.
        if (!is_array($keys)) {
            return array_key_exists($keys, $this->array);
        }

        // Check keys.
        $keys = $this->validateKeys($keys);

        // Get the last key.
        $last_key = array_pop($keys);

        // Find intermediary keys.
        $array = &$this->array;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array) || !is_array($array[$key])) {
                return false;
            }
            $array = &$array[$key];
        }

        return array_key_exists($last_key, $array);
    }

    /**
     * Validate a list of array keys.
     * 
     * @throws \InvalidArgumentException if invalid keys are passed.
     * 
     * @return array<int|string>
     */
    protected function validateKeys(array $keys): array
    {
        // Check array size.
        if (count($keys) < 1) {
            $message = 'Empty list of keys given.';
            throw new \InvalidArgumentException($message);
        }

        // Check each key.
        foreach ($keys as $key) {
            if (!is_int($key) && !is_string($key)) {
                $message = 'Array keys must be strings or integers.';
                throw new \InvalidArgumentException($message);
            }
        }

        return $keys;
    }
}
