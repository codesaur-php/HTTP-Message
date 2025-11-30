<?php

namespace codesaur\Http\Message;

use Psr\Http\Message\StreamInterface;

/**
 * Output stream – PHP-ийн output buffering-д суурилсан StreamInterface хэрэгжилт.
 *
 * Энэ класс нь HTTP хариуны body-г “шууд браузер руу хэвлэх”
 * зарчмаар ажилладаг тусгай stream юм. Уламжлалт файл эсвэл
 * memory stream-ээс ялгаатай нь:
 *
 * - OutputBuffer ашиглан ob_start() / ob_get_clean() дээр суурилна
 * - write() дуудахад шууд echo хийгдэж output-д гарна
 * - read(), seek(), rewind() зэрэг нь боломжгүй (unsupported)
 * - Буцааж унших шаардлагагүй зөвхөн бичих зориулалттай stream
 *
 * `Response`-ийн body-д энэ stream-г ашиглавал response контент
 * бодитоор шууд client рүү дамжина.
 */
class Output implements StreamInterface
{
    /**
     * PHP output buffer wrapper.
     *
     * @var OutputBuffer
     */
    protected OutputBuffer $buffer;
    
    /**
     * Output stream үүсэхэд output buffering автоматаар эхэлнэ.
     */
    public function __construct()
    {
        $this->buffer = new OutputBuffer();
        $this->buffer->start();
    }
    
    /**
     * Destructor.
     * 
     * Output buffering script дуусахад PHP автоматаар flush хийдэг тул
     * энд гараар endFlush() дуудах шаардлагагүй.
     */
    public function __destruct()
    {
        // PHP автомат flush хийнэ — endFlush() дуудах шаардлагагүй.
    }
    
    /**
     * OutputBuffer обьектыг буцаана.
     *
     * @return OutputBuffer
     */
    public function getBuffer(): OutputBuffer
    {
        return $this->buffer;
    }
    
    /**
     * Stream-ийн бүх контентыг string хэлбэрээр буцаана.
     * 
     * @return string
     *
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->getContents();
    }

    /**
     * Output buffering-ийг цэвэрлэж хаана.
     *
     * @return void
     *
     * @inheritdoc
     */
    public function close(): void
    {
        $this->buffer->endClean();
    }

    /**
     * Stream-ийн ресурсыг салгах боломжгүй.
     *
     * @return void
     *
     * @throws \RuntimeException
     *
     * @inheritdoc
     */
    public function detach()
    {
        throw new \RuntimeException(__CLASS__ . ' is not detachable');
    }

    /**
     * Stream-ийн нийт хэмжээ (buffer length)-г буцаана.
     *
     * @return int|null
     *
     * @inheritdoc
     */
    public function getSize(): ?int
    {
        return $this->buffer->getLength() ?: null;
    }

    /**
     * Seekable биш stream тул үргэлж 0 буцаана.
     *
     * @return int
     *
     * @inheritdoc
     */
    public function tell(): int
    {
        return 0;
    }

    /**
     * Output stream үргэлж EOF (унших боломжгүй) төлөвтэй байдаг.
     *
     * @return bool
     *
     * @inheritdoc
     */
    public function eof(): bool
    {
        return true;
    }

    /**
     * Seek дэмждэггүй.
     *
     * @return bool
     *
     * @inheritdoc
     */
    public function isSeekable(): bool
    {
        return false;
    }

    /**
     * Seek боломжгүй тул алдаа үүсгэнэ.
     *
     * @throws \RuntimeException
     *
     * @inheritdoc
     */
    public function seek(int $offset, int $whence = \SEEK_SET): void
    {
        throw new \RuntimeException(__CLASS__ . ' is not seekable');
    }

    /**
     * Rewind боломжгүй тул алдаа үүсгэнэ.
     *
     * @throws \RuntimeException
     *
     * @inheritdoc
     */
    public function rewind(): void
    {
        throw new \RuntimeException(__CLASS__ . ' is not rewindable');
    }

    /**
     * Энэ stream нь зөвхөн бичих боломжтой.
     *
     * @return bool
     *
     * @inheritdoc
     */
    public function isWritable(): bool
    {
        return true;
    }

    /**
     * Бичсэн string-ийг шууд echo хийж output руу дамжуулна.
     *
     * @param string $string
     *
     * @return int Бичигдсэн тэмдэгтийн тоо
     *
     * @inheritdoc
     */
    public function write(string $string): int
    {
        echo $string;
        
        return \strlen($string);
    }

    /**
     * Унших боломжгүй stream.
     *
     * @return bool
     *
     * @inheritdoc
     */
    public function isReadable(): bool
    {
        return false;
    }

    /**
     * Read дэмждэггүй тул алдаа үүсгэнэ.
     *
     * @throws \RuntimeException
     *
     * @inheritdoc
     */
    public function read(int $length): string
    {
        throw new \RuntimeException(__CLASS__ . ' is not readable');
    }

    /**
     * OutputBuffer-ийн одоогийн контентыг string хэлбэрээр буцаана.
     *
     * @return string
     *
     * @inheritdoc
     */
    public function getContents(): string
    {
        return (string) $this->buffer->getContents();
    }

    /**
     * Output stream-д metadata концепц байхгүй тул үргэлж null буцаана.
     *
     * @param string|null $key
     * @return null
     *
     * @inheritdoc
     */
    public function getMetadata(?string $key = null)
    {
        return null;
    }
}
