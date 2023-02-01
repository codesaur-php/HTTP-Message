<?php

namespace codesaur\Http\Message;

use Psr\Http\Message\ResponseInterface;
use Fig\Http\Message\StatusCodeInterface;

class NonBodyResponse extends Message implements ResponseInterface
{
    protected int $status = StatusCodeInterface::STATUS_OK;
    
    protected string $reasonPhrase = '';
    
    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $status = "STATUS_$code";
        $reasonPhraseClass = ReasonPrhase::class;
        if (!\defined("$reasonPhraseClass::$status")) {
            throw new \InvalidArgumentException(__CLASS__ . ': Invalid HTTP status code for response');
        }
        
        $clone = clone $this;
        $clone->status = $code;
        $clone->reasonPhrase = $reasonPhrase;
        
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase()
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
