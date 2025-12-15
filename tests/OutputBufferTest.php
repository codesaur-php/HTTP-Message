<?php

namespace codesaur\Http\Message\Tests;

use PHPUnit\Framework\TestCase;

use codesaur\Http\Message\OutputBuffer;

class OutputBufferTest extends TestCase
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

    public function testStart(): void
    {
        $buffer = new OutputBuffer();
        $buffer->start();
        
        echo 'Test';
        $this->assertGreaterThan(0, ob_get_level());
        ob_end_clean();
    }

    public function testStartCallback(): void
    {
        $buffer = new OutputBuffer();
        $called = false;
        
        $buffer->startCallback(function($content) use (&$called) {
            $called = true;
            return strtoupper($content);
        });
        
        echo 'test';
        ob_end_flush();
        
        $this->assertTrue($called);
    }

    public function testStartCompress(): void
    {
        $initialLevel = ob_get_level();
        $buffer = new OutputBuffer();
        $buffer->startCompress();
        
        echo '<div>   Test   </div>';
        // Get the raw content first
        $rawContent = ob_get_contents();
        
        // Manually call compress to test the compression
        $compressedContent = $buffer->compress($rawContent);
        
        // Clean up the buffer
        ob_end_clean();
        
        // Restore buffer level to initial state
        while (ob_get_level() > $initialLevel) {
            ob_end_clean();
        }
        
        $this->assertStringNotContainsString('   ', $compressedContent);
    }

    public function testFlush(): void
    {
        $buffer = new OutputBuffer();
        $buffer->start();
        
        echo 'Test';
        $buffer->flush();
        
        $this->assertGreaterThan(0, ob_get_level());
        ob_end_clean();
    }

    public function testEndClean(): void
    {
        $initialLevel = ob_get_level();
        $buffer = new OutputBuffer();
        $buffer->start();
        
        echo 'Test';
        $buffer->endClean();
        
        $this->assertEquals($initialLevel, ob_get_level());
    }

    public function testEndFlush(): void
    {
        $initialLevel = ob_get_level();
        ob_start();
        $buffer = new OutputBuffer();
        $buffer->start();
        
        echo 'Test';
        $buffer->endFlush();
        
        $this->assertEquals($initialLevel + 1, ob_get_level());
        ob_end_clean();
    }

    public function testGetLength(): void
    {
        $buffer = new OutputBuffer();
        $buffer->start();
        
        echo 'Test Content';
        $length = $buffer->getLength();
        
        $this->assertIsInt($length);
        $this->assertGreaterThan(0, $length);
        ob_end_clean();
    }

    public function testGetContents(): void
    {
        $buffer = new OutputBuffer();
        $buffer->start();
        
        echo 'Test Content';
        $contents = $buffer->getContents();
        
        $this->assertEquals('Test Content', $contents);
        ob_end_clean();
    }

    public function testGetContentsNoBuffer(): void
    {
        // Ensure no buffer is active for this test
        $initialLevel = ob_get_level();
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        $buffer = new OutputBuffer();
        $contents = $buffer->getContents();
        
        // When no buffer is active, getContents() returns null
        $this->assertNull($contents);
        
        // Restore initial buffer level
        while (ob_get_level() < $initialLevel) {
            ob_start();
        }
    }

    public function testCompress(): void
    {
        $buffer = new OutputBuffer();
        
        $html = '<div>   Test   </div>   <span>   Content   </span>';
        $compressed = $buffer->compress($html);
        
        $this->assertStringNotContainsString('   ', $compressed);
        $this->assertStringContainsString('<div>', $compressed);
        $this->assertStringContainsString('</div>', $compressed);
    }
}
