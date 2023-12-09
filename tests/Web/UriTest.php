<?php

namespace Tests\Web;

use Covaleski\Framework\Web\Uri;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Covaleski\Framework\Web\Uri
 */
class UriTest extends TestCase
{
    /**
     * @covers ::fromString
     * @covers ::__construct
     */
    public function testCanCreateFromString(): void
    {
        // Test with full URI.
        $text = 'https://'
            . 'john.doe:1234@'
            . 'foobar.com:8080'
            . '/foo/bar'
            . '?baz=true&lorem=ipsum'
            . '#foobar';
        $uri_a = Uri::fromString($text);
        $this->assertSame('https', $uri_a->scheme);
        $this->assertSame('john.doe:1234', $uri_a->userInfo);
        $this->assertSame('foobar.com', $uri_a->host);
        $this->assertSame(8080, $uri_a->port);
        $this->assertSame('foo/bar', $uri_a->path);
        $this->assertSame('baz=true&lorem=ipsum', $uri_a->query);
        $this->assertSame('foobar', $uri_a->fragment);

        // Test with partial URI.
        $uri_b = Uri::fromString('/path/to/resource');
        $this->assertNull($uri_b->scheme);
        $this->assertNull($uri_b->userInfo);
        $this->assertNull($uri_b->host);
        $this->assertNull($uri_b->port);
        $this->assertSame('path/to/resource', $uri_b->path);
        $this->assertNull($uri_b->query);
        $this->assertNull($uri_b->fragment);

        // Test with e-mail.
        $uri_c = Uri::fromString('mailto:jonh.doe@example.com');
        $this->assertSame('mailto', $uri_c->scheme);
        $this->assertNull($uri_c->userInfo);
        $this->assertNull($uri_c->host);
        $this->assertNull($uri_c->port);
        $this->assertSame('jonh.doe@example.com', $uri_c->path);
        $this->assertNull($uri_c->query);
        $this->assertNull($uri_c->fragment);

        // Test without scheme.
        $uri_d = Uri::fromString('//example.com/path/to/resource?foo=bar&baz');
        $this->assertNull($uri_d->scheme);
        $this->assertNull($uri_d->userInfo);
        $this->assertSame('example.com', $uri_d->host);
        $this->assertNull($uri_d->port);
        $this->assertSame('path/to/resource', $uri_d->path);
        $this->assertSame('foo=bar&baz', $uri_d->query);
        $this->assertNull($uri_d->fragment);
    }

    /**
     * @covers ::__toString
     */
    public function testMustUseValidUriToCreateFromString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Uri::fromString(':');
    }

    /**
     * @covers ::__construct
     */
    public function testSchemeAndHostAreCaseInsensitive(): void
    {
        $uri = new Uri (scheme: 'HTTP', host: 'ExAmPlE.com');
        $this->assertSame('http', $uri->scheme);
        $this->assertSame('example.com', $uri->host);
    }

    /**
     * @covers ::__construct
     */
    public function testTrimsPaths(): void
    {
        $paths = [
            'path/to/resource',
            '/path/to/resource/',
            'path/to/resource/',
        ];

        foreach ($paths as $path) {
            $uri = new Uri(path: $path);
            $this->assertSame('path/to/resource', $uri->path);
        }
    }

    // /**
    //  * @covers ::__toString
    //  */
    // public function testCanUseAsString(): void
    // {
    //     // Create and fill object.
    //     $uri = new Uri();
    //     $uri->host = 'company.com';
    //     $uri->path = 'blog';
    //     $this->assertSame('//company.com/blog', "{$uri}");
    // }

    // /**
    //  * Get an URI instance with fallback scheme and host.
    //  */
    // protected function getInstance(
    //     ?string $scheme = null,
    //     ?string $user_info = null,
    //     ?string $host = 'php.net',
    //     ?int $port = null,
    //     ?string $path = null,
    //     ?string $query = null,
    //     ?string $fragment = null,
    // ): Uri
    // {
    //     return new Uri(
    //         scheme: $scheme,
    //         user_info: $user_info,
    //         host: $host,
    //         port: $port,
    //         path: $path,
    //         query: $query,
    //         fragment: $fragment,
    //     );
    // }
}
