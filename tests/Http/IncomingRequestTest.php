<?php

declare(strict_types=1);

namespace Tests\Http;

use Covaleski\Framework\Http\IncomingRequest;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Covaleski\Framework\Http\IncomingRequest
 */
class IncomingRequestTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getPostVariables
     * @uses Covaleski\Framework\Data\ArrayReader::__construct
     * @uses Covaleski\Framework\Data\ArrayReader::getValue
     * @uses Covaleski\Framework\Data\ArrayReader::validateKeys
     * @uses Covaleski\Framework\Files\StringSource::__construct
     * @uses Covaleski\Framework\Files\StringSource::read
     * @uses Covaleski\Framework\Http\AbstractIncomingMessage::__construct
     * @uses Covaleski\Framework\Http\AbstractMessage::getBody
     * @uses Covaleski\Framework\Http\IncomingRequest::__construct
     */
    public function testCanInstantiateWithPostVariablesArray(): void
    {
        $request = $this->getInstance([
            'name' => 'John',
            'age' => '32',
            'fruits' => ['apple', 'tomato'],
        ]);

        $this->assertSame('', $request->getBody()->read(10));
        
        $fruit = $request->getPostVariables()->getValue(['fruits', 1]);
        $this->assertSame('tomato', $fruit);
    }

    /**
     * @covers ::__construct
     * @uses Covaleski\Framework\Data\ArrayReader::__construct
     * @uses Covaleski\Framework\Files\StringSource::__construct
     * @uses Covaleski\Framework\Files\StringSource::read
     * @uses Covaleski\Framework\Http\AbstractIncomingMessage::__construct
     * @uses Covaleski\Framework\Http\AbstractMessage::getBody
     * @uses Covaleski\Framework\Http\IncomingRequest::__construct
     */
    public function testCanInstantiateWithTextAndFile(): void
    {
        $text_request = $this->getInstance('Text content.');
        $text = $text_request->getBody()->read(13);
        $this->assertSame('Text content.', $text);

        $file = fopen('data://text/plain,File content.', 'r');
        $file_request = $this->getInstance($file);
        $text = $file_request->getBody()->read(13);
        $this->assertSame('File content.', $text);
    }

    /**
     * @covers ::__construct
     * @covers ::getParameters
     * @uses Covaleski\Framework\Data\ArrayReader::__construct
     * @uses Covaleski\Framework\Data\ArrayReader::getValue
     * @uses Covaleski\Framework\Files\StringSource::__construct
     * @uses Covaleski\Framework\Http\AbstractIncomingMessage::__construct
     * @uses Covaleski\Framework\Http\IncomingRequest::__construct
     */
    public function testCanGetParameters(): void
    {
        $request = $this->getInstance('Any content.');
        $parameter = $request->getParameters()->getValue('search');
        $this->assertSame('foobar', $parameter);
    }

    /**
     * Get a request pre-configured instance.
     */
    public function getInstance(mixed $content): IncomingRequest
    {
        // Create headers.
        $headers = [
            'Authorization' => 'Basic john.doe:1234',
        ];

        // Create parameters.
        $parameters = [
            'page' => '2',
            'search' => 'foobar',
        ];

        // Create request.
        return new IncomingRequest(
            content_or_post: $content,
            headers: $headers,
            parameters: $parameters,
        );
    }
}
