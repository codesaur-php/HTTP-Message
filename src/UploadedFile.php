<?php

namespace codesaur\Http\Message;

use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    protected ?string $name;
    
    protected ?string $type;
    
    protected ?int $size;
    
    protected int $error;
    
    protected string $tmp_name;
    
    private bool $_moved = false;
    
    function __construct(string $tmp_name, ?string $name, ?string $type, ?int $size, int $error)
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
    public function getError()
    {
        return $this->error;
    }

    /**
     * {@inheritdoc}
     */
    public function getStream()
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function moveTo($targetPath)
    {
        if (empty($targetPath)) {
            throw new \InvalidArgumentException('Invalid target path!');
        }

        if (empty($this->tmp_name)) {
            throw new \InvalidArgumentException('Upload file path not found!');
        }
        
        if ($this->_moved) {
            throw new \RuntimeException(sprintf('Uploaded file already moved from %s!', $this->tmp_name));
        }
                
        switch ($this->error) {
            case \UPLOAD_ERR_OK:
                break;
            case \UPLOAD_ERR_NO_FILE:
                throw new \RuntimeException('No file sent!');
            case \UPLOAD_ERR_INI_SIZE:
            case \UPLOAD_ERR_FORM_SIZE:
                throw new \RuntimeException('Exceeded filesize limit!');
            default:
                throw new \RuntimeException('Unknown errors on file upload!');
        }

        if (\PHP_SAPI == 'cli') {
            if (!rename($this->tmp_name, $targetPath)) {
                throw new \RuntimeException(sprintf('Error moving uploaded file %s to %s!', $this->tmp_name, $targetPath));
            }
        } else {
            if (!copy($this->tmp_name, $targetPath)) {
                throw new \RuntimeException(sprintf('Error moving uploaded file %s to %s!', $this->tmp_name, $targetPath));
            }

            if (!unlink($this->tmp_name)) {
                throw new \RuntimeException(sprintf('Error removing uploaded temp file %s!', $this->tmp_name));
            }
        }
        
        $this->_moved = true;
    }
}
