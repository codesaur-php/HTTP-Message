<?php

namespace codesaur\Http\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * HTTP Message-ийн үндсэн abstract хэрэгжилт.
 *
 * Энэ класс нь PSR-7 стандартын MessageInterface-ийн бүх үндсэн
 * функцүүдийг хэрэгжүүлэх суурь загвар юм. Request болон Response
 * объектууд энэ ангийг өргөтгөн ашиглана.
 *
 * Онцлог:
 * - HTTP протоколын дэмжигдэх хувилбаруудыг шалгана
 * - Header-үүдийг нэрээр нь case-insensitive байдлаар хадгална
 * - StreamInterface төрлийн message body ажиллуулна
 * - Бүх mutable өөрчлөлтүүд нь clone (immutable) зарчмаар буцаана
 */
abstract class Message implements MessageInterface
{
    /**
     * Дэмжигдэх HTTP протоколын хувилбарууд.
     * 
     * @var string[]
     */
    const HTTP_PROTOCOL_VERSIONS = [
        '1',
        '1.0',
        '1.1',
        '2',
        '2.0',
        '3',
        '3.0'
    ];
    
    /**
     * Message-ийн протоколын үндсэн хувилбар.
     *
     * @var string
     */
    protected string $protocolVersion = '1.1';
    
    /**
     * HTTP header-үүдийг хадгалах массив.
     * Key нь header-ийн нэр (uppercase), value нь массив хэлбэрт утгууд.
     *
     * @var array
     */
    protected array $headers = [];
    
    /**
     * Message body-г илэрхийлэх stream объект.
     *
     * @var StreamInterface|null
     */
    protected ?StreamInterface $body = null;

    /**
     * HTTP протоколын одоогийн хувилбарыг буцаана.
     *
     * @inheritdoc
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * HTTP протоколын хувилбарыг шинэчилсэн клон объект буцаана.
     *
     * @param string $version Дэмжигдэх протоколын хувилбар
     *
     * @throws \InvalidArgumentException Хэрэв буруу хувилбар өгвөл
     *
     * @return MessageInterface
     *
     * @inheritdoc
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
     * Message-ийн бүх header-үүдийг массив хэлбэрээр буцаана.
     *
     * @return array<string,array>
     *
     * @inheritdoc
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Тухайн нэртэй header байгаа эсэхийг шалгана.
     *
     * @param string $name Header-ийн нэр
     *
     * @inheritdoc
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[\strtoupper($name)]);
    }

    /**
     * Тухайн header-ийн бүх утгыг массив хэлбэртэй буцаана.
     * Хэрэв байхгүй бол хоосон массив буцаана.
     *
     * @param string $name Header-ийн нэр
     *
     * @return array
     *
     * @inheritdoc
     */
    public function getHeader(string $name): array
    {
        return $this->headers[\strtoupper($name)] ?? [];
    }

    /**
     * Header-ийн утгуудыг нэг мөрөнд (comma-separated) буцаана.
     *
     * @param string $name Header-ийн нэр
     *
     * @return string
     *
     * @inheritdoc
     */
    public function getHeaderLine(string $name): string
    {
        $values = $this->getHeader($name);
        return \implode(',', $values);
    }

    /**
     * Header-ийг шууд дотооддоо тохируулдаг туслах функц.
     *
     * @param string       $name  Header-ийн нэр
     * @param string|array $value Header-ийн утга(ууд)
     *
     * @return void
     */
    protected function setHeader($name, $value)
    {
        if (\is_array($value)) {
            $this->headers[\strtoupper($name)] = $value;
        } else {
            $this->headers[\strtoupper($name)] = [$value];
        }
    }

    /**
     * Header-ийг overwrite хийсэн шинэ клон буцаана.
     *
     * @inheritdoc
     */
    public function withHeader(string $name, $value): MessageInterface
    {
        $clone = clone $this;
        $clone->setHeader($name, $value);
        return $clone;
    }

    /**
     * Header-т нэмэлт утга (append) хийсэн шинэ клон буцаана.
     *
     * @param string $name Header-ийн нэр
     * @param string|array $value Нэмэх утга(ууд)
     *
     * @return MessageInterface
     *
     * @inheritdoc
     */
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $clone = clone $this;
        if ($clone->hasHeader($name)) {
            if (\is_array($value)) {
                $clone->headers[\strtoupper($name)] = \array_merge($clone->headers[\strtoupper($name)], $value);
            } else {
                $clone->headers[\strtoupper($name)][] = $value;
            }
        } else {
            $clone->setHeader($name, $value);
        }
        return $clone;
    }

    /**
     * Тухайн header-ийг устгасан клон объект буцаана.
     *
     * @param string $name Устгах header-ийн нэр
     *
     * @return MessageInterface
     *
     * @inheritdoc
     */
    public function withoutHeader(string $name): MessageInterface
    {
        $clone = clone $this;
        if ($clone->hasHeader($name)) {
            unset($clone->headers[\strtoupper($name)]);
        }
        return $clone;
    }

    /**
     * Message body буюу StreamInterface объект буцаана.
     *
     * Body null бол (lazy initialization) хоосон php://temp stream
     * автоматаар үүсгэнэ. Энэ нь GET request зэрэг body шаардлагагүй
     * хүсэлтүүдэд memory хэмнэнэ.
     *
     * @return StreamInterface
     *
     * @inheritdoc
     */
    public function getBody(): StreamInterface
    {
        if ($this->body === null) {
            // Lazy initialization: хоосон stream үүсгэх
            $resource = \fopen('php://temp', 'r+');
            $this->body = new Stream($resource);
        }
        
        return $this->body;
    }

    /**
     * Шинэ body-г тохируулсан клон объект буцаана.
     *
     * @param StreamInterface $body
     *
     * @return MessageInterface
     *
     * @inheritdoc
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }
}
