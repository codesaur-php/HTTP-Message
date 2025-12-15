<?php

namespace codesaur\Http\Message\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

use codesaur\Http\Message\Request;
use codesaur\Http\Message\Uri;

class RequestTest extends TestCase
{
    public function testGetRequestTarget(): void
    {
        $request = new Request();
        $this->assertEquals('/', $request->getRequestTarget());
    }

    public function testGetRequestTargetWithUri(): void
    {
        $uri = new Uri();
        $uri->setPath('/api/users');
        $uri->setQuery('id=1');
        $uri->setFragment('top');
        
        $request = new Request();
        $request = $request->withUri($uri);
        
        $this->assertEquals('/api/users?id=1#top', $request->getRequestTarget());
    }

    public function testWithRequestTarget(): void
    {
        $request = new Request();
        $newRequest = $request->withRequestTarget('*');
        
        $this->assertNotSame($request, $newRequest);
        $this->assertEquals('*', $newRequest->getRequestTarget());
    }

    public function testGetMethod(): void
    {
        $request = new Request();
        $this->assertEquals('', $request->getMethod());
    }

    public function testWithMethod(): void
    {
        $request = new Request();
        $newRequest = $request->withMethod('GET');
        
        $this->assertNotSame($request, $newRequest);
        $this->assertEquals('', $request->getMethod());
        $this->assertEquals('GET', $newRequest->getMethod());
    }

    public function testWithMethodInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $request = new Request();
        $request->withMethod('INVALID');
    }

    public function testGetUri(): void
    {
        $request = new Request();
        $uri = $request->getUri();
        $this->assertInstanceOf(UriInterface::class, $uri);
    }

    public function testWithUri(): void
    {
        $request = new Request();
        $uri = new Uri();
        $uri->setHost('example.com');
        $uri->setScheme('https');
        
        $newRequest = $request->withUri($uri);
        
        $this->assertNotSame($request, $newRequest);
        $this->assertSame($uri, $newRequest->getUri());
    }

    public function testWithUriPreserveHost(): void
    {
        $request = new Request();
        $request = $request->withHeader('Host', 'original.com');
        
        $uri = new Uri();
        $uri->setHost('new.com');
        
        $newRequest = $request->withUri($uri, true);
        $this->assertEquals('original.com', $newRequest->getHeaderLine('Host'));
        
        $newRequest2 = $request->withUri($uri, false);
        $this->assertEquals('new.com', $newRequest2->getHeaderLine('Host'));
    }
}
