<?php

namespace codesaur\Http\Message;

use Psr\Http\Message\StreamInterface;

/**
 * PSR-7 StreamInterface хэрэгжилт - файл resource дээр суурилсан.
 *
 * Энэ класс нь PHP resource (fopen() буцаасан) дээр суурилсан
 * StreamInterface хэрэгжилт юм. Request body-д ашиглагдана.
 */
class Stream implements StreamInterface
{
    /**
     * Stream resource.
     *
     * @var resource|null
     */
    private $resource;

    /**
     * Stream үүсгэх.
     *
     * @param resource $resource PHP stream resource (fopen() буцаасан)
     *
     * @throws \InvalidArgumentException Хэрэв $resource нь resource биш бол
     */
    public function __construct($resource)
    {
        if (!\is_resource($resource)) {
            throw new \InvalidArgumentException('Stream must be a resource');
        }
        
        $this->resource = $resource;
    }

    /**
     * Stream-ийн бүх контентыг string хэлбэрээр буцаана.
     *
     * @return string Stream-ийн контент
     *
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->getContents();
    }

    /**
     * Stream-ийн resource-г хааж, stream-г хаана.
     *
     * @return void
     *
     * @inheritdoc
     */
    public function close(): void
    {
        if ($this->resource !== null) {
            \fclose($this->resource);
            $this->resource = null;
        }
    }

    /**
     * Stream-ийн resource-г салгаж, stream-г хаана.
     *
     * Resource-г буцаана, гэхдээ stream-г дахин ашиглах боломжгүй болгоно.
     *
     * @return resource|null Stream resource эсвэл null (detached бол)
     *
     * @inheritdoc
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    /**
     * Stream-ийн хэмжээг (bytes) буцаана.
     *
     * @return int|null Stream-ийн хэмжээ эсвэл null (detached бол)
     *
     * @inheritdoc
     */
    public function getSize(): ?int
    {
        if ($this->resource === null) {
            return null;
        }
        
        $stats = \fstat($this->resource);
        return $stats['size'] ?? null;
    }

    /**
     * Stream-ийн одоогийн байрлалыг (position) буцаана.
     *
     * @return int Stream-ийн одоогийн байрлал (bytes)
     *
     * @throws \RuntimeException Stream detached бол эсвэл байрлал тодорхойлох боломжгүй бол
     *
     * @inheritdoc
     */
    public function tell(): int
    {
        if ($this->resource === null) {
            throw new \RuntimeException('Stream is detached');
        }
        
        $result = \ftell($this->resource);
        if ($result === false) {
            throw new \RuntimeException('Unable to determine stream position');
        }
        return $result;
    }

    /**
     * Stream-ийн төгсгөлд хүрсэн эсэхийг шалгана (EOF - End Of File).
     *
     * @return bool true бол EOF, false бол унших боломжтой
     *
     * @inheritdoc
     */
    public function eof(): bool
    {
        if ($this->resource === null) {
            return true;
        }
        return \feof($this->resource);
    }

    /**
     * Stream seek хийх боломжтой эсэхийг шалгана.
     *
     * @return bool true бол seekable, false бол биш
     *
     * @inheritdoc
     */
    public function isSeekable(): bool
    {
        if ($this->resource === null) {
            return false;
        }
        $meta = \stream_get_meta_data($this->resource);
        return $meta['seekable'];
    }

    /**
     * Stream-ийн байрлалыг өөрчлөнө (seek).
     *
     * @param int $offset Шинэ байрлал (bytes)
     * @param int $whence SEEK_SET (эхлэл), SEEK_CUR (одоогийн), SEEK_END (төгсгөл)
     *
     * @return void
     *
     * @throws \RuntimeException Stream seekable биш эсвэл seek хийх боломжгүй бол
     *
     * @inheritdoc
     */
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }
        if (\fseek($this->resource, $offset, $whence) === -1) {
            throw new \RuntimeException('Unable to seek to stream position');
        }
    }

    /**
     * Stream-ийн байрлалыг эхлэл рүү буцаана (rewind).
     *
     * @return void
     *
     * @throws \RuntimeException Stream seekable биш бол
     *
     * @inheritdoc
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * Stream бичих боломжтой эсэхийг шалгана.
     *
     * @return bool true бол writable, false бол биш
     *
     * @inheritdoc
     */
    public function isWritable(): bool
    {
        if ($this->resource === null) {
            return false;
        }
        
        $meta = \stream_get_meta_data($this->resource);
        $mode = $meta['mode'];
        return strpbrk($mode, 'waxc+') !== false;
    }

    /**
     * Stream-д мэдээлэл бичнэ.
     *
     * @param string $string Бичих string
     *
     * @return int Бичигдсэн тэмдэгтийн тоо
     *
     * @throws \RuntimeException Stream writable биш эсвэл бичих боломжгүй бол
     *
     * @inheritdoc
     */
    public function write(string $string): int
    {
        if (!$this->isWritable()) {
            throw new \RuntimeException('Stream is not writable');
        }
        
        $result = \fwrite($this->resource, $string);
        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }
        return $result;
    }

    /**
     * Stream унших боломжтой эсэхийг шалгана.
     *
     * @return bool true бол readable, false бол биш
     *
     * @inheritdoc
     */
    public function isReadable(): bool
    {
        if ($this->resource === null) {
            return false;
        }
        
        $meta = \stream_get_meta_data($this->resource);
        $mode = $meta['mode'];
        return \strpbrk($mode, 'r+') !== false;
    }

    /**
     * Stream-аас мэдээлэл уншина.
     *
     * @param int $length Унших тэмдэгтийн тоо
     *
     * @return string Уншсан мэдээлэл
     *
     * @throws \RuntimeException Stream readable биш эсвэл унших боломжгүй бол
     *
     * @inheritdoc
     */
    public function read(int $length): string
    {
        if (!$this->isReadable()) {
            throw new \RuntimeException('Stream is not readable');
        }
        
        $result = \fread($this->resource, $length);
        if ($result === false) {
            throw new \RuntimeException('Unable to read from stream');
        }
        return $result;
    }

    /**
     * Stream-ийн үлдсэн бүх контентыг уншина.
     *
     * @return string Stream-ийн үлдсэн контент
     *
     * @throws \RuntimeException Stream detached бол эсвэл унших боломжгүй бол
     *
     * @inheritdoc
     */
    public function getContents(): string
    {
        if ($this->resource === null) {
            throw new \RuntimeException('Stream is detached');
        }
        
        $contents = \stream_get_contents($this->resource);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read stream contents');
        }
        return $contents;
    }

    /**
     * Stream-ийн metadata-г буцаана.
     *
     * @param string|null $key Metadata key (null бол бүх metadata)
     *
     * @return array|mixed|null
     *         - array: Бүх metadata (key null бол)
     *         - mixed: Тодорхой key-ийн утга
     *         - null: Key олдохгүй эсвэл stream detached бол
     *
     * @inheritdoc
     */
    public function getMetadata(?string $key = null)
    {
        if ($this->resource === null) {
            return $key === null ? [] : null;
        }
        
        $meta = \stream_get_meta_data($this->resource);
        if ($key === null) {
            return $meta;
        }
        return $meta[$key] ?? null;
    }
}
