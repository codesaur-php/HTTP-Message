<?php

namespace codesaur\Http\Message\Tests;

use PHPUnit\Framework\TestCase;

use codesaur\Http\Message\Uri;

class UriTest extends TestCase
{
    public function testGetScheme(): void
    {
        $uri = new Uri();
        $this->assertEquals('', $uri->getScheme());
    }

    public function testSetScheme(): void
    {
        $uri = new Uri();
        $uri->setScheme('https');
        $this->assertEquals('https', $uri->getScheme());
    }

    public function testSetSchemeInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $uri = new Uri();
        $uri->setScheme('ftp');
    }

    public function testWithScheme(): void
    {
        $uri = new Uri();
        $newUri = $uri->withScheme('https');
        
        $this->assertNotSame($uri, $newUri);
        $this->assertEquals('', $uri->getScheme());
        $this->assertEquals('https', $newUri->getScheme());
    }

    public function testGetAuthority(): void
    {
        $uri = new Uri();
        $this->assertEquals('', $uri->getAuthority());
        
        $uri->setHost('example.com');
        $this->assertEquals('example.com', $uri->getAuthority());
        
        $uri->setPort(8080);
        $this->assertEquals('example.com:8080', $uri->getAuthority());
        
        $uri->setUserInfo('user', 'pass');
        $this->assertEquals('user:pass@example.com:8080', $uri->getAuthority());
    }

    public function testGetUserInfo(): void
    {
        $uri = new Uri();
        $this->assertEquals('', $uri->getUserInfo());
        
        $uri->setUserInfo('user');
        $this->assertEquals('user', $uri->getUserInfo());
        
        $uri->setUserInfo('user', 'pass');
        $this->assertEquals('user:pass', $uri->getUserInfo());
    }

    public function testWithUserInfo(): void
    {
        $uri = new Uri();
        $newUri = $uri->withUserInfo('user', 'pass');
        
        $this->assertNotSame($uri, $newUri);
        $this->assertEquals('user:pass', $newUri->getUserInfo());
    }

    public function testGetHost(): void
    {
        $uri = new Uri();
        $this->assertEquals('', $uri->getHost());
        
        $uri->setHost('example.com');
        $this->assertEquals('example.com', $uri->getHost());
    }

    public function testSetHostIPv6(): void
    {
        $uri = new Uri();
        $uri->setHost('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
        $this->assertEquals('[2001:0db8:85a3:0000:0000:8a2e:0370:7334]', $uri->getHost());
    }

    public function testWithHost(): void
    {
        $uri = new Uri();
        $newUri = $uri->withHost('example.com');
        
        $this->assertNotSame($uri, $newUri);
        $this->assertEquals('example.com', $newUri->getHost());
    }

    public function testGetPort(): void
    {
        $uri = new Uri();
        $this->assertNull($uri->getPort());
        
        $uri->setPort(8080);
        $this->assertEquals(8080, $uri->getPort());
    }

    public function testGetPortDefault(): void
    {
        $uri = new Uri();
        $uri->setScheme('http');
        $uri->setPort(80);
        $this->assertNull($uri->getPort());
        
        $uri->setScheme('https');
        $uri->setPort(443);
        $this->assertNull($uri->getPort());
    }

    public function testSetPortInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $uri = new Uri();
        $uri->setPort(70000);
    }

    public function testWithPort(): void
    {
        $uri = new Uri();
        $newUri = $uri->withPort(8080);
        
        $this->assertNotSame($uri, $newUri);
        $this->assertEquals(8080, $newUri->getPort());
    }

    public function testGetPath(): void
    {
        $uri = new Uri();
        $this->assertEquals('', $uri->getPath());
        
        $uri->setPath('/api/users');
        $this->assertEquals('/api/users', $uri->getPath());
    }

    public function testWithPath(): void
    {
        $uri = new Uri();
        $newUri = $uri->withPath('/api/users');
        
        $this->assertNotSame($uri, $newUri);
        $this->assertEquals('/api/users', $newUri->getPath());
    }

    public function testGetQuery(): void
    {
        $uri = new Uri();
        $this->assertEquals('', $uri->getQuery());
        
        $uri->setQuery('id=1&name=test');
        $this->assertEquals('id=1&name=test', $uri->getQuery());
    }

    public function testWithQuery(): void
    {
        $uri = new Uri();
        $newUri = $uri->withQuery('id=1');
        
        $this->assertNotSame($uri, $newUri);
        $this->assertEquals('id=1', $newUri->getQuery());
    }

    public function testGetFragment(): void
    {
        $uri = new Uri();
        $this->assertEquals('', $uri->getFragment());
        
        $uri->setFragment('section');
        $this->assertEquals('section', $uri->getFragment());
    }

    public function testWithFragment(): void
    {
        $uri = new Uri();
        $newUri = $uri->withFragment('section');
        
        $this->assertNotSame($uri, $newUri);
        $this->assertEquals('section', $newUri->getFragment());
    }

    public function testToString(): void
    {
        $uri = new Uri();
        $uri->setScheme('https');
        $uri->setHost('example.com');
        $uri->setPath('/api/users');
        $uri->setQuery('id=1');
        $uri->setFragment('top');
        
        $expected = 'https://example.com/api/users?id=1#top';
        $this->assertEquals($expected, (string) $uri);
    }
}
