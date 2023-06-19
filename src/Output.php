<?php

namespace codesaur\Http\Message;

use Psr\Http\Message\StreamInterface;

class Output implements StreamInterface
{
    protected OutputBuffer $buffer;
    
    public function __construct()
    {
        $this->buffer = new OutputBuffer();
        if (($_ENV['CODESAUR_OUTPUT_COMPRESS'] ?? false) === true) {
            $this->buffer->startCompress();
        } else {
            $this->buffer->start();
        }
    }
    
    public function __destruct()
    {
        // If outbut buffering is still active when the script ends, PHP outputs it automatically.
        // In effect, every script ends with ob_end_flush(). Thus we don't really need to call endFlush!
        // $this->buffer->endFlush();
    }
    
    public function getBuffer(): OutputBuffer
    {
        return $this->buffer;
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->getContents();
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        $this->buffer->endClean();
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        return \RuntimeException(__CLASS__ . ' is not detachable');
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        return $this->buffer->getLength() ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function seek(int $offset, int $whence = \SEEK_SET): void
    {
        \RuntimeException(__CLASS__ . ' is not seekable');
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        \RuntimeException(__CLASS__ . ' is not rewindable');
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $string): int
    {
        echo $string;
        
        return \strlen($string);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $length): string
    {
        throw new \RuntimeException(__CLASS__ . ' is not readable');
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        return (string) $this->buffer->getContents();
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata(?string $key = null)
    {
        return null;
    }
}
