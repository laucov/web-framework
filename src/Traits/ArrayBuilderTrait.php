<?php

namespace Covaleski\Framework\Traits;

/**
 * Has properties and methods to search and build array.
 */
trait ArrayBuilderTrait
{
    /**
     * Get an array value.
     */
    protected function getArrayValue(
        array $array,
        int|string|array $name,
        mixed $default_value,
    ): mixed {
        // Get single key value.
        if (!is_array($name)) {
            return $array[$name] ?? $default_value;
        }

        // Check keys.
        if (!$this->validateArrayKeys($name)) {
            $message = 'All keys must be integers or strings.';
            throw new \InvalidArgumentException($message);
        }

        // Get nested value.
        $value = $array;
        foreach ($name as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return $default_value;
            }
            $value = $value[$key];
        }

        return $value;
    }

    /**
     * Remove an array value.
     */
    protected function removeArrayValue(
        array &$array,
        int|string|array $name,
    ): void {
        // Remove single key.
        if (!is_array($name)) {
            unset($array[$name]);
            return;
        }

        // Check keys.
        if (!$this->validateArrayKeys($name)) {
            $message = 'All keys must be integers or strings.';
            throw new \InvalidArgumentException($message);
        }

        // Remove nested value.
        $last_key = array_pop($name);
        foreach ($name as $key) {
            if (!array_key_exists($key, $array)) {
                return;
            }
            $array = &$array[$key];
        }
        unset($array[$last_key]);
    }
    
    /**
     * Set an array value.
     */
    protected function setArrayValue(
        array &$array,
        int|string|array $name,
        mixed $value,
    ): void {
        // Set single key value.
        if (!is_array($name)) {
            $array[$name] = $value;
            return;
        }

        // Check keys.
        if (!$this->validateArrayKeys($name)) {
            $message = 'All keys must be integers or strings.';
            throw new \InvalidArgumentException($message);
        }
        
        // Set nested value.
        $last_key = array_pop($name);
        foreach ($name as $key) {
            if (!is_array($array[$key] ?? null)) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[$last_key] = $value;
    }

    /**
     * Check if the given array contains only valid array keys.
     */
    protected function validateArrayKeys(array $keys): bool
    {
        if (count($keys) < 1) {
            return false;
        }

        foreach ($keys as $key) {
            if (!is_int($key) && !is_string($key)) {
                return false;
            }
        }

        return true;
    }
}
