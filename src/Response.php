<?php

namespace codesaur\Http\Message;

use Psr\Http\Message\ResponseInterface;
use Fig\Http\Message\StatusCodeInterface;

/**
 * PSR-7 стандартын HTTP Response (серверийн хариу) объектын хэрэгжилт.
 *
 * Энэ класс нь HTTP хариуны:
 *   - статус код
 *   - reason phrase
 *   - headers
 *   - протоколын хувилбар
 *   - body stream (default: Output stream)
 *
 * зэрэг үндсэн параметрүүдийг immutable (clone хийж буцаадаг)
 * зарчмаар удирддаг.
 *
 * Онцлог:
 *   - Анхдагч body нь Output stream тул write() → шууд echo хийгдэнэ.
 *   - Status/ReasonPhrase нь ReasonPhrase класст заасан RFC стандарттай
 *     status code-уудыг баталгаатайгаар ашиглана.
 *   - PSR-7 ResponseInterface-ийн шаардлагуудыг бүрэн хангана.
 */
class Response extends Message implements ResponseInterface
{
    /**
     * HTTP статус код.
     *
     * @var int
     */
    protected int $status = StatusCodeInterface::STATUS_OK;
    
    /**
     * Custom reason phrase.
     * Хэрэв хоосон байвал ReasonPhrase классын стандарт утгыг ашиглана.
     *
     * @var string
     */
    protected string $reasonPhrase = '';
    
    /**
     * Response үүсэх үед body-г Output stream болгон тохируулна.
     * Ингэснээр write() хийгдэх бүрт шууд хэрэглэгч рүү дамжина.
     */
    public function __construct()
    {
        $this->body = new Output();
    }
    
    /**
     * HTTP хариуны статус кодыг буцаана.
     *
     * @inheritdoc
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * Шинэ статус код болон reason phrase тохируулах immutable setter.
     *
     * @param int    $code          RFC стандартын HTTP статус код
     * @param string $reasonPhrase  Custom текстэн тайлбар (optional)
     *
     * @throws \InvalidArgumentException
     *          — код integer биш бол
     *          — ReasonPhrase класст байхгүй статус код бол
     *
     * @return ResponseInterface
     *
     * @inheritdoc
     */
    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        if (!\is_int($code)) {
            throw new \InvalidArgumentException(__CLASS__ . ': HTTP status code must be an integer');
        }
        
        $status = "STATUS_$code";
        $reasonPhraseClass = ReasonPrhase::class;

        // RFC-ийн статус код мөн эсэхийг шалгана
        if (!\defined("$reasonPhraseClass::$status")) {
            throw new \InvalidArgumentException(__CLASS__ . ': Invalid HTTP status code for response');
        }
        
        $clone = clone $this;
        $clone->status = (int) $code;

        // Custom reason phrase өгөгдсөн эсэхээс хамааруулна
        if (empty($reasonPhrase)) {
            $clone->reasonPhrase = '';
        } else {
            $clone->reasonPhrase = (string) $reasonPhrase;
        }

        return $clone;
    }

    /**
     * Reason phrase (хариуны текстэн тайлбар)-г буцаана.
     *
     * Давтамж:
     *   - Custom утга өгсөн бол тэрийг
     *   - Үгүй бол ReasonPhrase::STATUS_xxx тогтмолоос стандарт утга
     *   - Стандарт утга олдохгүй бол хоосон string буцаана
     *
     * @return string
     *
     * @inheritdoc
     */
    public function getReasonPhrase(): string
    {
        if (!empty($this->reasonPhrase)) {
            return $this->reasonPhrase;
        }
        
        $status = "STATUS_$this->status";
        $reasonPhrase = ReasonPrhase::class;

        if (\defined("$reasonPhrase::$status")) {
            return \constant("$reasonPhrase::$status");
        }
        
        return '';
    }
}
