<?php

namespace codesaur\Http\Message\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

use codesaur\Http\Message\Message;
use codesaur\Http\Message\Request;

/**
 * Test class for Message abstract class.
 * 
 * Since Message is abstract, we test it through Request class.
 */
class MessageTest extends TestCase
{
    private int $initialBufferLevel = 0;

    protected function setUp(): void
    {
        $this->initialBufferLevel = ob_get_level();
    }

    protected function tearDown(): void
    {
        // Clean up only buffers created during this test
        while (ob_get_level() > $this->initialBufferLevel) {
            ob_end_clean();
        }
    }

    public function testGetProtocolVersion(): void
    {
        $request = new Request();
        $this->assertEquals('1.1', $request->getProtocolVersion());
    }

    public function testWithProtocolVersion(): void
    {
        $request = new Request();
        $newRequest = $request->withProtocolVersion('2.0');
        
        $this->assertNotSame($request, $newRequest);
        $this->assertEquals('1.1', $request->getProtocolVersion());
        $this->assertEquals('2.0', $newRequest->getProtocolVersion());
    }

    public function testWithProtocolVersionInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $request = new Request();
        $request->withProtocolVersion('4.0');
    }

    public function testWithProtocolVersion3(): void
    {
        $request = new Request();
        $newRequest = $request->withProtocolVersion('3.0');
        
        $this->assertNotSame($request, $newRequest);
        $this->assertEquals('1.1', $request->getProtocolVersion());
        $this->assertEquals('3.0', $newRequest->getProtocolVersion());
    }

    public function testGetHeaders(): void
    {
        $request = new Request();
        $headers = $request->getHeaders();
        $this->assertIsArray($headers);
    }

    public function testHasHeader(): void
    {
        $request = new Request();
        $this->assertFalse($request->hasHeader('Content-Type'));
        
        $request = $request->withHeader('Content-Type', 'application/json');
        $this->assertTrue($request->hasHeader('Content-Type'));
        $this->assertTrue($request->hasHeader('content-type'));
        $this->assertTrue($request->hasHeader('CONTENT-TYPE'));
    }

    public function testGetHeader(): void
    {
        $request = new Request();
        $this->assertEquals([], $request->getHeader('Content-Type'));
        
        $request = $request->withHeader('Content-Type', 'application/json');
        $this->assertEquals(['application/json'], $request->getHeader('Content-Type'));
    }

    public function testGetHeaderLine(): void
    {
        $request = new Request();
        $this->assertEquals('', $request->getHeaderLine('Content-Type'));
        
        $request = $request->withHeader('Content-Type', 'application/json');
        $this->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
        
        $request = $request->withHeader('Accept', ['application/json', 'text/html']);
        $this->assertEquals('application/json,text/html', $request->getHeaderLine('Accept'));
    }

    public function testWithHeader(): void
    {
        $request = new Request();
        $newRequest = $request->withHeader('Content-Type', 'application/json');
        
        $this->assertNotSame($request, $newRequest);
        $this->assertFalse($request->hasHeader('Content-Type'));
        $this->assertTrue($newRequest->hasHeader('Content-Type'));
        $this->assertEquals(['application/json'], $newRequest->getHeader('Content-Type'));
    }

    public function testWithHeaderArray(): void
    {
        $request = new Request();
        $newRequest = $request->withHeader('Accept', ['application/json', 'text/html']);
        
        $this->assertEquals(['application/json', 'text/html'], $newRequest->getHeader('Accept'));
    }

    public function testWithAddedHeader(): void
    {
        $request = new Request();
        $request = $request->withHeader('Accept', 'application/json');
        $newRequest = $request->withAddedHeader('Accept', 'text/html');
        
        $this->assertNotSame($request, $newRequest);
        $this->assertEquals(['application/json'], $request->getHeader('Accept'));
        $this->assertEquals(['application/json', 'text/html'], $newRequest->getHeader('Accept'));
    }

    public function testWithAddedHeaderNew(): void
    {
        $request = new Request();
        $newRequest = $request->withAddedHeader('Content-Type', 'application/json');
        
        $this->assertTrue($newRequest->hasHeader('Content-Type'));
        $this->assertEquals(['application/json'], $newRequest->getHeader('Content-Type'));
    }

    public function testWithoutHeader(): void
    {
        $request = new Request();
        $request = $request->withHeader('Content-Type', 'application/json');
        $newRequest = $request->withoutHeader('Content-Type');
        
        $this->assertNotSame($request, $newRequest);
        $this->assertTrue($request->hasHeader('Content-Type'));
        $this->assertFalse($newRequest->hasHeader('Content-Type'));
    }

    public function testGetBody(): void
    {
        $request = new Request();
        $body = $request->getBody();
        $this->assertInstanceOf(StreamInterface::class, $body);
    }

    public function testWithBody(): void
    {
        $request = new Request();
        $newBody = new \codesaur\Http\Message\Output();
        $newRequest = $request->withBody($newBody);
        
        $this->assertNotSame($request, $newRequest);
        $this->assertNotSame($request->getBody(), $newRequest->getBody());
        $this->assertSame($newBody, $newRequest->getBody());
    }
}
