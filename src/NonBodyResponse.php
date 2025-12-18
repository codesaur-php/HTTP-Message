<?php

namespace codesaur\Http\Message;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Fig\Http\Message\StatusCodeInterface;

/**
 * Body stream агуулаагүй HTTP хариу (Response)-ийн минимал хэрэгжилт.
 *
 * Энэ класс нь PSR-7-ийн ResponseInterface-ийг мөрдөх бөгөөд 
 * зөвхөн HTTP статус код, reason phrase, headers болон protocol-г удирдах зориулалттай.
 * 
 * **Үндсэн зорилго:**
 * Output buffer-тэй шууд `echo`, `print` эсвэл бусад PHP output функцүүд ашиглан
 * browser/клиент рүү шууд хэвлэх үед зориулсан класс юм. Body stream огт агуулаагүй,
 * учир нь контент нь output buffer-аас шууд browser руу дамжина.
 *
 * **{@see Response} классын ялгаа:**
 * - `Response`: Body stream агуулдаг (default: {@see Output} stream). 
 *   `$response->getBody()->write()` ашиглан body-д бичнэ.
 * - `NonBodyResponse`: Body stream огт байхгүй. 
 *   Шууд `echo`, `print` эсвэл output buffer-аар хэвлэнэ.
 *
 * **Хэрэглээ:**
 * ```php
 * // Output buffer-тэй шууд хэвлэх
 * $response = new NonBodyResponse();
 * $response = $response->withStatus(200);
 * echo "Hello World"; // Шууд browser руу хэвлэгдэнэ
 * 
 * // Redirect
 * $redirect = (new NonBodyResponse())
 *     ->withStatus(302)
 *     ->withHeader('Location', '/new-page');
 * // Body байхгүй, зөвхөн headers илгээнэ
 * ```
 *
 * Ийм төрлийн хариу нь ихэвчлэн:
 *   - Redirect (301, 302, 303, 307, 308)
 *   - 204 No Content
 *   - 304 Not Modified
 *   - Output buffer-аар шууд хэвлэх үед
 *   - эсвэл body шаардлагагүй бусад серверийн хариуд
 * ашиглагдана.
 *
 * **Онцлог:**
 * - Message суурь классыг өргөтгөн headers болон protocol-той ажиллана
 * - Body stream огт тохируулаагүй (null). `getBody()` дуудахад PSR-7 стандартын
 *   шаардлагыг хангахын тулд хоосон php://temp stream үүсгэгдэнэ, гэхдээ энэ stream-г
 *   ашиглахгүй байх нь зөв (output buffer-тэй шууд browser руу хэвлэх).
 * - Статус кодыг ReasonPhrase тогтмолуудаас баталгаажуулж шалгана
 * - Output buffer-тэй шууд echo, print хийх үед тохиромжтой
 */
class NonBodyResponse extends Message implements ResponseInterface
{
    /**
     * HTTP статус код.
     * Анхдагч нь 200 OK.
     *
     * @var int
     */
    protected int $status = StatusCodeInterface::STATUS_OK;
    
    /**
     * Хэрэв custom reason phrase тохируулсан бол энд хадгалагдана.
     * Хоосон бол ReasonPhrase классын тогтмолуудаас автоматаар уншина.
     *
     * @var string
     */
    protected string $reasonPhrase = '';
    
    /**
     * HTTP хариуны статус кодыг буцаана.
     *
     * @return int
     *
     * @inheritdoc
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * Өгөгдсөн статус код болон reason phrase-тэй шинэ Response объект буцаана.
     * (PSR-7 immutable зарчим)
     *
     * @param int    $code          HTTP статус код
     * @param string $reasonPhrase  Нэмэлт тайлбар (заавал биш)
     *
     * @throws \InvalidArgumentException Хэрэв статус код танигдаагүй бол
     *
     * @return ResponseInterface
     *
     * @inheritdoc
     */
    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $status = "STATUS_$code";
        $reasonPhraseClass = ReasonPhrase::class;

        // Статус код ReasonPhrase класст тодорхойлсон эсэхийг шалгана
        if (!\defined("$reasonPhraseClass::$status")) {
            throw new \InvalidArgumentException(
                __CLASS__ . ': Invalid HTTP status code for response'
            );
        }
        
        $clone = clone $this;
        $clone->status = $code;
        $clone->reasonPhrase = $reasonPhrase;
        
        return $clone;
    }

    /**
     * Хариуны reason phrase-г буцаана.
     *
     * - Хэрэв хэрэглэгч custom reason phrase өгсөн бол шууд тэрийг буцаана.
     * - Хоосон бол ReasonPhrase классын статус кодын тогтмолоос автоматаар уншина.
     * - Таарахгүй бол хоосон string буцаана.
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

    /**
     * Body stream буцаана.
     *
     * NonBodyResponse нь body stream агуулаагүй тул exception шиднэ.
     * Энэ классын зорилго нь output buffer-тэй шууд `echo`, `print` ашиглан
     * browser руу хэвлэх тул body stream шаардлагагүй.
     * 
     * Хэрэв body stream шаардлагатай бол `Response` классыг ашиглах хэрэгтэй.
     *
     * @return StreamInterface
     *
     * @throws \RuntimeException NonBodyResponse нь body stream дэмжихгүй
     *
     * @inheritdoc
     */
    public function getBody(): StreamInterface
    {
        throw new \RuntimeException(
            __CLASS__ . ' does not support body stream. ' .
            'Use output buffer with echo/print for direct output, or use Response class for body stream support.'
        );
    }
}
