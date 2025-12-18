<?php

namespace codesaur\Http\Message;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Fig\Http\Message\RequestMethodInterface;

/**
 * PSR-7 стандартын HTTP Request объектын хэрэгжилт.
 *
 * Энэ класс нь HTTP хүсэлтийн үндсэн бүх шинж чанарууд болох:
 *   - request target
 *   - HTTP method
 *   - URI
 *   - headers
 *   - протоколын хувилбар
 *   - body (Message ангид)
 *
 * зэрэг утгуудыг immutable зарчмаар (clone) удирдаж зохицуулна.
 *
 * Хэрэглээ:
 *   $request = (new Request())
 *      ->withMethod('GET')
 *      ->withUri($uri)
 *      ->withHeader('Accept', 'application/json');
 */
class Request extends Message implements RequestInterface
{
    /**
     * HTTP хүсэлтийн method (GET, POST, PUT, DELETE, …).
     *
     * @var string
     */
    protected string $method = '';
    
    /**
     * Хүсэлтийн URI объект (scheme, host, path, query…).
     *
     * @var UriInterface|null
     */
    protected ?UriInterface $uri = null;
    
    /**
     * Request target - тухайн хүсэлтийн зорилтот зам.
     *
     * Жишээ:
     *   "/products?id=1#top"
     *
     * @var string
     */
    protected string $requestTarget = '';
    
    /**
     * Request target-ийг буцаана.
     *
     * Custom утга тохируулсан бол тэрийг буцаана.
     *
     * Хоосон бол URI-ийн path + query + fragment-ийг ашиглан target үүсгэнэ.
     * Хэрэв URI байхгүй бол "/" буцаана.
     *
     * @return string Request target string
     *
     * @inheritdoc
     */
    public function getRequestTarget(): string
    {
        if (!empty($this->requestTarget)) {
            return $this->requestTarget;
        }
        
        if (!isset($this->uri)) {
            return '/';
        }

        // Path нь аль хэдийн encoded эсвэл unencoded байж болно
        // PSR-7 стандартын дагуу хадгалсан утгыг шууд ашиглана
        $path = $this->getUri()->getPath();
        $requestTarget = '/' . \ltrim($path, '/');
        
        // Query string нь аль хэдийн encoded байх ёстой
        $query = $this->getUri()->getQuery();
        if ($query != '') {
            $requestTarget .= '?' . $query;
        }
        
        // Fragment нь аль хэдийн encoded байх ёстой
        $fragment = $this->getUri()->getFragment();
        if ($fragment != '') {
            $requestTarget .= '#' . $fragment;
        }

        return $requestTarget;
    }
    
    /**
     * Request target-ийг шинэчилсэн клон объект буцаана.
     *
     * @param string $requestTarget
     *
     * @return RequestInterface
     *
     * @inheritdoc
     */
    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        $clone = clone $this;
        $clone->requestTarget = $requestTarget;
        return $clone;
    }
    
    /**
     * HTTP method-ийг буцаана (жишээ: GET, POST).
     *
     * @return string
     *
     * @inheritdoc
     */
    public function getMethod(): string
    {
        return $this->method;
    }
    
    /**
     * HTTP method-ийг шинэчилсэн клон буцаана.
     *
     * Method нь PSR-7-д заагдсан стандарт HTTP арга байх ёстой.
     * 
     * @param string $method  Method (GET, POST, PUT, DELETE…)
     *
     * @throws \InvalidArgumentException Хэрэв method буруу бол
     *
     * @return RequestInterface
     *
     * @inheritdoc
     */
    public function withMethod(string $method): RequestInterface
    {
        $uMethod = \strtoupper($method);
        $commonHTTPmethod = RequestMethodInterface::class . "::METHOD_$uMethod";

        if (!\defined($commonHTTPmethod)) {
            throw new \InvalidArgumentException(__CLASS__ . ": Invalid HTTP method [$method]");
        }
        
        $clone = clone $this;
        $clone->method = $uMethod;
        return $clone;
    }
    
    /**
     * Request-ийн URI-г буцаана.
     *
     * @return UriInterface
     *
     * @inheritdoc
     */
    public function getUri(): UriInterface
    {
        if ($this->uri === null) {
            $this->uri = new Uri();
        }
        return $this->uri;
    }
    
    /**
     * Шинэ URI-тай request клон буцаана.
     *
     * @param UriInterface $uri
     * @param bool $preserveHost  true бол Host header-ийг хадгална.
     *
     * Host header удирдлага:
     *   - preserveHost = false → URI host-оор Host header-ийг солино.
     *   - preserveHost = true → зөвхөн Host header байхгүй үед тохируулна.
     *
     * @return RequestInterface
     *
     * @inheritdoc
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        $clone = clone $this;
        $clone->uri = $uri;
        
        if ($preserveHost == false) {
            if ($uri->getHost() != '') {
                $clone->setHeader('Host', $uri->getHost());
            }
            
            return $clone;
        }

        // preserveHost == true
        if ($this->getHeaderLine('Host') == ''
            && $uri->getHost() != ''
        ) {
            $clone->setHeader('Host', $uri->getHost());
        }

        return $clone;
    }
}
