<?php

namespace codesaur\Http\Message;

use RuntimeException;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\StreamInterface;

class UploadedFile implements UploadedFileInterface
{
    protected $name;
    protected $type;
    protected $size;
    protected $tmp_name;
    protected $error = 0;
    
    function __construct($tmp_name, $name, $type, $size, $error)
    {
        $this->tmp_name = $tmp_name;
        
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->error = $error;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getClientFilename()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientMediaType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * {@inheritdoc}
     */
    public function getStream(): StreamInterface
    {
        throw new RuntimeException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function moveTo($targetPath)
    {
        throw new RuntimeException('Not implemented');
    }
}
