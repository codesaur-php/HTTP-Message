<?php

namespace codesaur\Http\Message\Tests;

use PHPUnit\Framework\TestCase;

use codesaur\Http\Message\UploadedFile;

class UploadedFileTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $this->tmpFile = sys_get_temp_dir() . '/test_upload_' . uniqid() . '.txt';
        file_put_contents($this->tmpFile, 'test content');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }

    public function testGetClientFilename(): void
    {
        $file = new UploadedFile($this->tmpFile, 'test.txt', 'text/plain', 12, UPLOAD_ERR_OK);
        $this->assertEquals('test.txt', $file->getClientFilename());
    }

    public function testGetClientMediaType(): void
    {
        $file = new UploadedFile($this->tmpFile, 'test.txt', 'text/plain', 12, UPLOAD_ERR_OK);
        $this->assertEquals('text/plain', $file->getClientMediaType());
    }

    public function testGetSize(): void
    {
        $file = new UploadedFile($this->tmpFile, 'test.txt', 'text/plain', 12, UPLOAD_ERR_OK);
        $this->assertEquals(12, $file->getSize());
    }

    public function testGetError(): void
    {
        $file = new UploadedFile($this->tmpFile, 'test.txt', 'text/plain', 12, UPLOAD_ERR_OK);
        $this->assertEquals(UPLOAD_ERR_OK, $file->getError());
    }

    public function testGetStream(): void
    {
        $this->expectException(\RuntimeException::class);
        $file = new UploadedFile($this->tmpFile, 'test.txt', 'text/plain', 12, UPLOAD_ERR_OK);
        $file->getStream();
    }

    public function testMoveTo(): void
    {
        $targetPath = sys_get_temp_dir() . '/moved_' . uniqid() . '.txt';
        $file = new UploadedFile($this->tmpFile, 'test.txt', 'text/plain', 12, UPLOAD_ERR_OK);
        
        $file->moveTo($targetPath);
        
        $this->assertFileExists($targetPath);
        $this->assertEquals('test content', file_get_contents($targetPath));
        
        if (file_exists($targetPath)) {
            unlink($targetPath);
        }
    }

    public function testMoveToEmptyPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $file = new UploadedFile($this->tmpFile, 'test.txt', 'text/plain', 12, UPLOAD_ERR_OK);
        $file->moveTo('');
    }

    public function testMoveToTwice(): void
    {
        $this->expectException(\RuntimeException::class);
        $targetPath1 = sys_get_temp_dir() . '/moved1_' . uniqid() . '.txt';
        $targetPath2 = sys_get_temp_dir() . '/moved2_' . uniqid() . '.txt';
        
        $file = new UploadedFile($this->tmpFile, 'test.txt', 'text/plain', 12, UPLOAD_ERR_OK);
        $file->moveTo($targetPath1);
        $file->moveTo($targetPath2);
        
        if (file_exists($targetPath1)) {
            unlink($targetPath1);
        }
        if (file_exists($targetPath2)) {
            unlink($targetPath2);
        }
    }

    public function testMoveToNoFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $file = new UploadedFile('', null, null, null, UPLOAD_ERR_NO_FILE);
        $file->moveTo(sys_get_temp_dir() . '/test.txt');
    }

    public function testJsonSerialize(): void
    {
        $file = new UploadedFile($this->tmpFile, 'test.txt', 'text/plain', 12, UPLOAD_ERR_OK);
        $data = $file->jsonSerialize();
        
        $this->assertIsArray($data);
        $this->assertEquals('test.txt', $data['name']);
        $this->assertEquals('text/plain', $data['type']);
    }
}
