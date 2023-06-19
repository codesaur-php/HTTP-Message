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
    
    protected StreamInterface $body;

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
    public function withProtocolVersion(string $version): MessageInterface
    {
        if (!\in_array($version, self::HTTP_PROTOCOL_VERSIONS)) {
            throw new \InvalidArgumentException(__CLASS__ . ": Invalid HTTP protocol version [$version]");
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
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[\strtoupper($name)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader(string $name): array
    {
        return $this->headers[\strtoupper($name)] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine(string $name): string
    {
        $values = $this->getHeader($name);
        return \implode(',', $values);
    }

    public function setHeader($name, $value)
    {
        if (\is_array($value)) {
            $this->headers[\strtoupper($name)] = $value;
        } else {
            $this->headers[\strtoupper($name)] = [$value];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader(string $name, $value): MessageInterface
    {
        $clone = clone $this;
        $clone->setHeader($name, $value);
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $clone = clone $this;
        if ($this->hasHeader($name)) {
            if (\is_array($value)) {
                $this->headers[\strtoupper($name)] += $value;
            } else {
                $this->headers[\strtoupper($name)][] = $value;
            }
        } else {
            $this->setHeader($name, $value);
        }
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader(string $name): MessageInterface
    {
        $clone = clone $this;
        if ($this->hasHeader($name)) {
            unset($this->headers[\strtoupper($name)]);
        }
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): StreamInterface
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
