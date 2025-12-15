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
 * Body Stream-ийн тохиргоо:
 *   - Анхдагч body нь {@see Output} stream тул {@see Response::__construct()}
 *     дуудахад автоматаар output buffer stream үүсгэнэ.
 *   - Output stream нь write() хийгдэх бүрт шууд browser/клиент рүү
 *     хэвлэдэг (output buffering ашиглаж).
 *   - Хэрэв та шууд хэвлэхгүй, response body-г өөр stream дээр авахыг
 *     хүсвэл {@see Message::withBody()} method ашиглан body-г ямар ч
 *     StreamInterface implementation-д холбож болно:
 *     - {@see Stream} (php://temp, php://memory, файл stream)
 *     - {@see Output} (шууд browser руу хэвлэх)
 *     - Бусад custom StreamInterface хэрэгжилт
 *
 * Жишээ:
 * ```php
 * // Default: Output stream (шууд browser руу хэвлэх)
 * $response = new Response();
 * $response->getBody()->write('Hello'); // Шууд browser руу хэвлэгдэнэ
 *
 * // Memory stream ашиглах (response body-г memory дээр авах)
 * $memoryStream = new Stream(fopen('php://memory', 'r+'));
 * $response = (new Response())->withBody($memoryStream);
 * $response->getBody()->write('Hello'); // Memory дээр хадлагдана
 * $content = $response->getBody()->getContents(); // "Hello"
 * ```
 *
 * Онцлог:
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
     * Response үүсэх үед body-г Output buffering stream болгон тохируулна.
     *
     * Анхдагч body нь {@see Output} stream тул write() хийгдэх бүрт
     * шууд browser/клиент рүү хэвлэгдэнэ (output buffering ашигладаг).
     *
     * Хэрэв та шууд хэвлэхгүй, response body-г өөр stream дээр авахыг
     * хүсвэл {@see Message::withBody()} method ашиглан body-г ямар ч
     * StreamInterface implementation-д холбож болно:
     *
     * - {@see Stream} (php://temp, php://memory, файл stream) - memory эсвэл
     *   файл дээр хадгалах
     * - {@see Output} - шууд browser руу хэвлэх (default)
     * - Бусад custom StreamInterface хэрэгжилт
     *
     * Жишээ:
     * ```php
     * // Default: шууд browser руу хэвлэх
     * $response = new Response();
     * $response->getBody()->write('Hello'); // Шууд хэвлэгдэнэ
     *
     * // Memory stream ашиглах
     * $memoryStream = new Stream(fopen('php://memory', 'r+'));
     * $response = (new Response())->withBody($memoryStream);
     * $response->getBody()->write('Hello'); // Memory дээр хадлагдана
     * ```
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
     *          - код integer биш бол
     *          - ReasonPhrase класст байхгүй статус код бол
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
        $reasonPhraseClass = ReasonPhrase::class;

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
        $reasonPhrase = ReasonPhrase::class;

        if (\defined("$reasonPhrase::$status")) {
            return \constant("$reasonPhrase::$status");
        }
        
        return '';
    }
}
