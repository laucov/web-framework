<?php

namespace Covaleski\Framework\Http\Traits;

/**
 * Has properties and methods common to request objects.
 */
trait RequestTrait
{
    /**
     * HTTP method.
     */
    protected string $method = 'GET';

    /**
     * URL parameters.
     */
    protected array $parameters = [];

    /**
     * Get the request method.
     * 
     * Always returns the method name in uppercase characters.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get a URL parameter.
     */
    public function getParameter(string $name): null|string
    {
        $value = $this->parameters[$name] ?? null;

        if (is_int($value) || is_string($value)) {
            return $value;
        } else {
            return null;
        }
    }

    /**
     * Get a list of URL parameters.
     * 
     * @var null|string[]
     */
    public function getParameterList(string $name): null|array
    {
        $list = $this->parameters[$name] ?? null;

        if (!is_array($list) || !array_is_list($list)) {
            return null;
        }

        foreach ($list as $item) {
            if (!is_string($item)) {
                return null;
            }
        }

        return $list;
    }
}
