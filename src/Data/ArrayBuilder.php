<?php

namespace Laucov\WebFramework\Data;

/**
 * Controls the contents of an array.
 */
class ArrayBuilder extends ArrayReader
{
    /**
     * Stored array.
     */
    protected array $array;

    /**
     * Remove a value.
     */
    public function removeValue(int|string|array $keys): static
    {
        // Resolve single key.
        if (!is_array($keys)) {
            unset($this->array[$keys]);
            return $this;
        }

        // Check keys.
        $keys = $this->validateKeys($keys);

        // Get the last key.
        $last_key = array_pop($keys);

        // Find/fill intermediary keys.
        $array = &$this->array;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array) || !is_array($array[$key])) {
                return $this;
            }
            $array = &$array[$key];
        }

        // Remove value.
        unset($array[$last_key]);

        return $this;
    }

    /**
     * Set a value.
     */
    public function setValue(int|string|array $keys, mixed $value): static
    {
        // Resolve single key.
        if (!is_array($keys)) {
            $this->array[$keys] = $value;
            return $this;
        }

        // Check keys.
        $keys = $this->validateKeys($keys);

        // Get the last key.
        $last_key = array_pop($keys);

        // Find/fill intermediary keys.
        $array = &$this->array;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }

        // Set value.
        $array[$last_key] = $value;

        return $this;
    }
}
