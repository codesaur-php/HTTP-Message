<?php declare(strict_types=1);

namespace codesaur\Http\Message;

use InvalidArgumentException;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Fig\Http\Message\RequestMethodInterface;

class Request extends Message implements RequestInterface
{
    protected $method;
    protected $uri;
    protected $requestTarget;
    
    /**
     * {@inheritdoc}
     */
    public function getRequestTarget(): string
    {
        if (!empty($this->requestTarget)) {
            return $this->requestTarget;
        } elseif (!$this->getUri() instanceof UriInterface) {
            return '/';
        }

        $path = rawurldecode($this->getUri()->getPath());
        $requestTarget = '/' . ltrim($path, '/');
        
        $query = $this->getUri()->getQuery();
        if ($query !== '') {
            $requestTarget .= '?' . rawurldecode($query);
        }
        
        $fragment = $this->getUri()->getFragment();
        if ($fragment !== '') {
            $requestTarget .= '#' . rawurldecode($fragment);
        }

        return $requestTarget;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget): RequestInterface
    {
        $clone = clone $this;
        $clone->requestTarget = $requestTarget;

        return $clone;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMethod(): string
    {
        return $this->method ?? '';
    }
    
    /**
     * {@inheritdoc}
     */
    public function withMethod($method): RequestInterface
    {
        $uMethod = strtoupper($method);
        $commonHTTPmethod = RequestMethodInterface::class . "::METHOD_$uMethod";
        if (!defined($commonHTTPmethod)) {
            throw new InvalidArgumentException("Invalid HTTP method [$method]!");
        }
        
        $clone = clone $this;
        $clone->method = $uMethod;

        return $clone;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getUri(): ?UriInterface
    {
        return $this->uri;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false): RequestInterface
    {
        $clone = clone $this;
        $clone->uri = $uri;
        
        if (!$preserveHost) {
            if ($uri->getHost() !== '') {
                $clone->setHeader('Host', $uri->getHost());
            }
            
            return $clone;
        }

        if ($this->getHeaderLine('Host') === ''
                && $uri->getHost() !== ''
        ) {
            $clone->setHeader('Host', $uri->getHost());
        }

        return $clone;
    }
}
