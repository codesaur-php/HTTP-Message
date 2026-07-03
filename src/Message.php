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
     * Key нь header-ийн анхны бичиглэлтэй нэр, value нь массив хэлбэрт утгууд.
     *
     * @var array
     */
    protected array $headers = [];

    /**
     * Header-ийн uppercase нэрийг анхны бичиглэлтэй нэр рүү буулгах map.
     * Case-insensitive хайлт хийхэд ашиглана.
     *
     * @var array<string,string>
     */
    protected array $headerNames = [];

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
        return isset($this->headerNames[\strtoupper($name)]);
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
        $normalized = \strtoupper($name);
        if (!isset($this->headerNames[$normalized])) {
            return [];
        }
        return $this->headers[$this->headerNames[$normalized]];
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
     * Header-ийн нэрийг шалгах туслах функц.
     *
     * RFC 7230 token дүрмээр header нэр зөвшөөрөгдсөн тэмдэгтүүдээс
     * бүрдэх ёстой (CRLF injection-с хамгаална).
     *
     * @param string $name Header-ийн нэр
     *
     * @throws \InvalidArgumentException Нэр хоосон эсвэл буруу тэмдэгттэй бол
     *
     * @return void
     */
    protected function validateHeaderName(string $name)
    {
        if (!\preg_match("/^[a-zA-Z0-9!#$%&'*+.^_`|~-]+$/", $name)) {
            throw new \InvalidArgumentException(__CLASS__ . ": Invalid header name [$name]");
        }
    }

    /**
     * Header-ийн утгуудыг шалгах туслах функц.
     *
     * Утга нь string байх ёстой бөгөөд CR, LF, NUL тэмдэгт агуулж
     * болохгүй (header injection-с хамгаална).
     *
     * @param array $values Header-ийн утгууд
     *
     * @throws \InvalidArgumentException Утга буруу төрөлтэй эсвэл хориотой тэмдэгттэй бол
     *
     * @return void
     */
    protected function validateHeaderValues(array $values)
    {
        foreach ($values as $value) {
            if (!\is_string($value) && !\is_numeric($value)) {
                throw new \InvalidArgumentException(__CLASS__ . ': Header value must be a string');
            }
            if (\preg_match("/[\r\n\0]/", (string) $value)) {
                throw new \InvalidArgumentException(__CLASS__ . ': Header value must not contain CR, LF or NUL characters');
            }
        }
    }

    /**
     * Header-ийг шууд дотооддоо тохируулдаг туслах функц.
     *
     * Нэрний анхны бичиглэлийг хадгалж, case-insensitive хайлтад
     * зориулж headerNames map-д бүртгэнэ.
     *
     * @param string       $name  Header-ийн нэр
     * @param string|array $value Header-ийн утга(ууд)
     *
     * @throws \InvalidArgumentException Нэр эсвэл утга буруу бол
     *
     * @return void
     */
    protected function setHeader($name, $value)
    {
        $values = \is_array($value) ? \array_values($value) : [$value];

        $this->validateHeaderName($name);
        $this->validateHeaderValues($values);

        $normalized = \strtoupper($name);
        if (isset($this->headerNames[$normalized])) {
            // Өмнө нь өөр бичиглэлээр орсон header байвал устгана
            unset($this->headers[$this->headerNames[$normalized]]);
        }

        $this->headerNames[$normalized] = $name;
        $this->headers[$name] = $values;
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
        $normalized = \strtoupper($name);
        if (isset($clone->headerNames[$normalized])) {
            $values = \is_array($value) ? \array_values($value) : [$value];

            $clone->validateHeaderName($name);
            $clone->validateHeaderValues($values);

            // Анх бүртгэгдсэн бичиглэлтэй нэр дээр нь утгуудыг нэмнэ
            $original = $clone->headerNames[$normalized];
            $clone->headers[$original] = \array_merge($clone->headers[$original], $values);
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
        $normalized = \strtoupper($name);
        if (isset($clone->headerNames[$normalized])) {
            unset(
                $clone->headers[$clone->headerNames[$normalized]],
                $clone->headerNames[$normalized]
            );
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
