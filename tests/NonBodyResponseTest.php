<?php

namespace codesaur\Http\Message\Tests;

use PHPUnit\Framework\TestCase;
use Fig\Http\Message\StatusCodeInterface;

use codesaur\Http\Message\NonBodyResponse;

class NonBodyResponseTest extends TestCase
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
        $response = new NonBodyResponse();
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    public function testWithStatus(): void
    {
        $response = new NonBodyResponse();
        $newResponse = $response->withStatus(301);
        
        $this->assertNotSame($response, $newResponse);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(301, $newResponse->getStatusCode());
    }

    public function testWithStatusInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $response = new NonBodyResponse();
        $response->withStatus(999);
    }

    public function testGetReasonPhrase(): void
    {
        $response = new NonBodyResponse();
        $this->assertEquals('OK', $response->getReasonPhrase());
        
        $response = $response->withStatus(204);
        $this->assertEquals('No Content', $response->getReasonPhrase());
        
        $response = $response->withStatus(301);
        $this->assertEquals('Moved Permanently', $response->getReasonPhrase());
    }

    public function testWithStatusCustomReasonPhrase(): void
    {
        $response = new NonBodyResponse();
        $newResponse = $response->withStatus(200, 'Custom OK');
        
        $this->assertEquals('Custom OK', $newResponse->getReasonPhrase());
    }
}
