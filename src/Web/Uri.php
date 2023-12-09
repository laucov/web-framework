<?php

namespace Covaleski\Framework\Web;

/**
 * Stores information from a Unique Resource Identifier.
 */
class Uri
{
    /**
     * Create an instance from a string URI.
     */
    public static function fromString(string $uri): Uri
    {
        // Parse URI.
        $components = parse_url($uri);
        if (!is_array($components)) {
            throw new \InvalidArgumentException('Invalid URI given.');
        }

        // Get user info parts.
        $user = $components['user'] ?? '';
        $pass = $components['pass'] ?? '';
        $user_info = $user || $pass
            ? ($user . ($user && $pass ? ':' : '') . $pass)
            : null;

        // Create object.
        $object = new Uri(
            scheme: $components['scheme'] ?? null,
            user_info: $user_info,
            host: $components['host'] ?? null,
            port: $components['port'] ?? null,
            path: $components['path'] ?? null,
            query: $components['query'] ?? null,
            fragment: $components['fragment'] ?? null,
        );

        return $object;
    }

    /**
     * Host.
     */
    public readonly null|string $host;

    /**
     * Fragment.
     */
    public readonly null|string $fragment;

    /**
     * Path.
     */
    public readonly null|string $path;

    /**
     * Port.
     */
    public readonly null|int $port;

    /**
     * Scheme.
     */
    public readonly null|string $scheme;

    /**
     * Query.
     */
    public readonly null|string $query;

    /**
     * User.
     */
    public readonly null|string $userInfo;

    /**
     * Create the URI instance.
     */
    public function __construct(
        ?string $scheme = null,
        ?string $user_info = null,
        ?string $host = null,
        ?int $port = null,
        ?string $path = null,
        ?string $query = null,
        ?string $fragment = null,
    ) {
        $this->scheme = empty($scheme) ? null : strtolower($scheme);
        $this->userInfo = $user_info;
        $this->host = empty($host) ? null : strtolower($host);
        $this->port = $port;
        $this->path = trim($path, '/');
        $this->query = $query;
        $this->fragment = $fragment;
    }

    // /**
    //  * Get the URI string from this object.
    //  */
    // public function __toString(): string
    // {
    //     // Check if we have either a host or path.
    //     if (empty($this->host) && empty($this->path)) {
    //         $message = 'Cannot build URI without host or path.';
    //         throw new \RuntimeException($message);
    //     }

    //     // Initialize URI.
    //     $uri = '';
        
    //     // Add scheme.
    //     if ($this->scheme !== null) {
    //         $uri .= $this->scheme . ':';
    //     }

    //     // Add authority.
    //     if ($this->host !== null) {
    //         $uri .= '//';
    //         // Add user and password.
    //         if ($this->user !== null) {
    //             $uri .= $this->user;
    //             if ($this->password !== null) {
    //                 $uri .= ':' . $this->password;
    //             }
    //             $uri .= '@';
    //         }
    //         $uri .= $this->host;
    //         if ($this->port !== null) {
    //             $uri .= ':' . $this->port;
    //         }
    //     }

    //     // Add path.
    //     if ($this->path !== null) {
    //         if ($this->host !== null) {
    //             $uri .= '/';
    //         }
    //         $uri .= $this->path;
    //     }

    //     // Add query.
    //     if ($this->query !== null) {
    //         $uri .= '?' . $this->query;
    //     }

    //     // Add fragment.
    //     if ($this->fragment !== null) {
    //         $uri .= '#' . $this->fragment;
    //     }

    //     return $uri;
    // }
}
