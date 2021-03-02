<?php declare(strict_types=1);

namespace codesaur\Http\Message;

use InvalidArgumentException;

use Psr\Http\Message\ResponseInterface;
use Fig\Http\Message\StatusCodeInterface;

class Response extends Message implements ResponseInterface
{
    protected $status = StatusCodeInterface::STATUS_OK;
    protected $reasonPhrase;
    
    function __construct()
    {
        $this->body = new Output();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        if (!is_int($code)) {
            throw new InvalidArgumentException('HTTP status code must be an integer!');
        }
        
        $status = "STATUS_$code";
        $reasonPhraseInterface = ReasonPrhaseInterface::class;
        if (!defined("$reasonPhraseInterface::$status")) {
            throw new InvalidArgumentException('Invalid HTTP status code for response!');
        }
        
        $clone = clone $this;
        $clone->status = $code;
        
        if (empty($reasonPhrase)) {
            $clone->reasonPhrase = null;
        } else {
            $clone->reasonPhrase = (string)$reasonPhrase;
        }
        
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase(): string
    {
        if (!empty($this->reasonPhrase)) {
            return $this->reasonPhrase;
        }
        
        $status = "STATUS_$this->status";
        $reasonPhraseInterface = ReasonPrhaseInterface::class;
        if (defined("$reasonPhraseInterface::$status")) {
            return constant("$reasonPhraseInterface::$status");
        }
        
        return '';
    }
}
