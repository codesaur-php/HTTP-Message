<?php

namespace codesaur\Http\Message\Tests;

use PHPUnit\Framework\TestCase;
use codesaur\Http\Message\Response;
use Fig\Http\Message\StatusCodeInterface;

class ResponseTest extends TestCase
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

    public function testGetStatusCode(): void
    {
        $response = new Response();
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    public function testWithStatus(): void
    {
        $response = new Response();
        $newResponse = $response->withStatus(404);
        
        $this->assertNotSame($response, $newResponse);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(404, $newResponse->getStatusCode());
    }

    public function testWithStatusInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $response = new Response();
        $response->withStatus(999);
    }

    public function testGetReasonPhrase(): void
    {
        $response = new Response();
        $this->assertEquals('OK', $response->getReasonPhrase());
        
        $response = $response->withStatus(404);
        $this->assertEquals('Not Found', $response->getReasonPhrase());
    }

    public function testWithStatusCustomReasonPhrase(): void
    {
        $response = new Response();
        $newResponse = $response->withStatus(200, 'Custom OK');
        
        $this->assertEquals('Custom OK', $newResponse->getReasonPhrase());
    }

    public function testResponseBody(): void
    {
        $response = new Response();
        $body = $response->getBody();
        $this->assertInstanceOf(\Psr\Http\Message\StreamInterface::class, $body);
    }
}

