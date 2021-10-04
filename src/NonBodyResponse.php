<?php

namespace codesaur\Http\Message;

use InvalidArgumentException;

use Psr\Http\Message\ResponseInterface;
use Fig\Http\Message\StatusCodeInterface;

class NonBodyResponse extends Message implements ResponseInterface
{
    protected $status = StatusCodeInterface::STATUS_OK;
    protected $reasonPhrase;
    
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
        $status = "STATUS_$code";
        $reasonPhraseClass = ReasonPrhase::class;
        if (!defined("$reasonPhraseClass::$status")) {
            throw new InvalidArgumentException(__CLASS__ . ': Invalid HTTP status code for response');
        }
        
        $clone = clone $this;
        $clone->status = (int)$code;        
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
        $reasonPhrase = ReasonPrhase::class;
        if (defined("$reasonPhrase::$status")) {
            return constant("$reasonPhrase::$status");
        }
        
        return '';
    }
}
