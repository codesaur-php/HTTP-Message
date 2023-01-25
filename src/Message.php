<?php

namespace codesaur\Http\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class Message implements MessageInterface
{
    const HTTP_PROTOCOL_VERSIONS = [
        '1',
        '1.0',
        '1.1',
        '2',
        '2.0'
    ];
    
    protected string $protocolVersion = '1.1';
    
    protected array $headers = [];
    
    protected ?StreamInterface $body = null;

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {
        if (!in_array($version, self::HTTP_PROTOCOL_VERSIONS)) {
            throw new \InvalidArgumentException(__CLASS__ . ": Invalid HTTP protocol version [$version]");
        }

        $clone = clone $this;
        $clone->protocolVersion = $version;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return isset($this->headers[strtoupper($name)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name)
    {
        return $this->headers[strtoupper($name)] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name)
    {
        $values = $this->getHeader($name);
        return implode(',', $values);
    }

    function setHeader($name, $value)
    {
        if (is_array($value)) {
            $this->headers[strtoupper($name)] = $value;
        } else {
            $this->headers[strtoupper($name)] = [$value];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {
        $clone = clone $this;
        $clone->setHeader($name, $value);
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {
        $clone = clone $this;
        if ($this->hasHeader($name)) {
            if (is_array($value)) {
                $this->headers[strtoupper($name)] += $value;
            } else {
                $this->headers[strtoupper($name)][] = $value;
            }
        } else {
            $this->setHeader($name, $value);
        }                
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name)
    {
        $clone = clone $this;
        if ($this->hasHeader($name)) {
            unset($this->headers[strtoupper($name)]);
        }
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }
}
