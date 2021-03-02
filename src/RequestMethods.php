<?php

namespace codesaur\Http\Message;

use ReflectionClass;

use Fig\Http\Message\RequestMethodInterface;

class RequestMethods implements RequestMethodInterface
{
    public function getMethods(): array
    {
        return (new ReflectionClass($this))->getConstants();
    }
}
