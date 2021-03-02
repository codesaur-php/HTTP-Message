<?php declare(strict_types=1);

namespace codesaur\Http\Message;

use InvalidArgumentException;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class Message implements MessageInterface
{
    const HTTP_PROTOCOL_VERSIONS = array(
        '1',
        '1.0',
        '1.1',
        '2',
        '2.0',
    );
    
    protected $protocolVersion = '1.1';    
    protected $headers = array();    
    protected $body;

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version): MessageInterface
    {
        if (!in_array($version, self::HTTP_PROTOCOL_VERSIONS, true)) {
            throw new InvalidArgumentException("Invalid HTTP protocol version [$version]!");
        }

        $clone = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name): bool
    {
        return isset($this->headers[strtoupper($name)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name): array
    {
        return $this->headers[strtoupper($name)] ?? array();
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name): string
    {
        $values = $this->getHeader($name);
        
        return implode(',', $values);
    }

    function setHeader($name, $value)
    {
        $this->headers[strtoupper($name)] = array($value);
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value): MessageInterface
    {
        $clone = clone $this;
        $clone->setHeader($name, $value);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value): MessageInterface
    {
        $clone = clone $this;
        if ($this->hasHeader($name)) {
            $this->headers[strtoupper($name)][] = $value;
        } else {
            $this->setHeader($name, $value);
        }
                
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name): MessageInterface
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
    public function getBody(): ?StreamInterface
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }
}
