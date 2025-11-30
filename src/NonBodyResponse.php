<?php

namespace codesaur\Http\Message;

use Psr\Http\Message\ResponseInterface;
use Fig\Http\Message\StatusCodeInterface;

/**
 * Body агуулаагүй HTTP хариу (Response)-ийн минимал хэрэгжилт.
 *
 * Энэ класс нь PSR-7-ийн ResponseInterface-ийг мөрдөх бөгөөд 
 * зөвхөн HTTP статус код болон reason phrase-г удирдах зориулалттай.
 * 
 * Ийм төрлийн хариу нь ихэвчлэн:
 *   - Redirect (301, 302…)
 *   - 204 No Content
 *   - 304 Not Modified
 *   - эсвэл body шаардлагагүй бусад серверийн хариуд
 * ашиглагдана.
 *
 * Онцлог:
 * - Message суурь классыг өргөтгөн headers болон protocol-той ажиллана
 * - Body байхгүй тул stream заавал тохируулах шаардлагагүй
 * - Статус кодыг ReasonPhrase тогтмолуудаас баталгаажуулж шалгана
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
        $reasonPhraseClass = ReasonPrhase::class;

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
        $reasonPhrase = ReasonPrhase::class;

        if (\defined("$reasonPhrase::$status")) {
            return \constant("$reasonPhrase::$status");
        }
        
        return '';
    }
}
