<?php

namespace codesaur\Http\Message\Tests;

use PHPUnit\Framework\TestCase;

use codesaur\Http\Message\Output;

class OutputTest extends TestCase
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

    public function testWrite(): void
    {
        ob_start();
        $output = new Output();
        $bytes = $output->write('Hello World');
        
        $this->assertEquals(11, $bytes);
        $content = ob_get_clean();
        $this->assertEquals('Hello World', $content);
    }

    public function testGetSize(): void
    {
        $output = new Output();
        $output->write('Test');
        
        $size = $output->getSize();
        $this->assertIsInt($size);
        $this->assertGreaterThanOrEqual(4, $size);
    }

    public function testTell(): void
    {
        $output = new Output();
        $this->assertEquals(0, $output->tell());
    }

    public function testEof(): void
    {
        $output = new Output();
        $this->assertTrue($output->eof());
    }

    public function testIsSeekable(): void
    {
        $output = new Output();
        $this->assertFalse($output->isSeekable());
    }

    public function testSeek(): void
    {
        $this->expectException(\RuntimeException::class);
        $output = new Output();
        $output->seek(0);
    }

    public function testRewind(): void
    {
        $this->expectException(\RuntimeException::class);
        $output = new Output();
        $output->rewind();
    }

    public function testIsWritable(): void
    {
        $output = new Output();
        $this->assertTrue($output->isWritable());
    }

    public function testIsReadable(): void
    {
        $output = new Output();
        $this->assertFalse($output->isReadable());
    }

    public function testRead(): void
    {
        $this->expectException(\RuntimeException::class);
        $output = new Output();
        $output->read(10);
    }

    public function testGetContents(): void
    {
        ob_start();
        $output = new Output();
        $output->write('Test Content');
        
        $contents = $output->getContents();
        $this->assertStringContainsString('Test Content', $contents);
        ob_end_clean();
    }

    public function testToString(): void
    {
        ob_start();
        $output = new Output();
        $output->write('Test');
        
        $string = (string) $output;
        $this->assertStringContainsString('Test', $string);
        ob_end_clean();
    }

    public function testClose(): void
    {
        $initialLevel = ob_get_level();
        $output = new Output();
        $output->write('Test');
        $output->close();
        
        // After close, the buffer created by Output should be cleaned
        // But other buffers might still exist
        $this->assertLessThanOrEqual($initialLevel, ob_get_level());
    }

    public function testDetach(): void
    {
        $this->expectException(\RuntimeException::class);
        $output = new Output();
        $output->detach();
    }

    public function testGetMetadata(): void
    {
        $output = new Output();
        $this->assertNull($output->getMetadata());
        $this->assertNull($output->getMetadata('key'));
    }
}
