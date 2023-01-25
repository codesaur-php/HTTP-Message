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
    
    function __destruct()
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
    public function __toString()
    {
        return $this->getContents();
    }

    /**
     * {@inheritdoc}
     */
    public function close()
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
    public function getSize()
    {
        return $this->buffer->getLength();
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = \SEEK_SET)
    {
        \RuntimeException(__CLASS__ . ' is not seekable');
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        \RuntimeException(__CLASS__ . ' is not rewindable');
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        echo $string;
        
        return strlen($string);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        throw new \RuntimeException(__CLASS__ . ' is not readable');
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        return (string) $this->buffer->getContents();
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        return null;
    }
}
