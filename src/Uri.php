<?php

namespace codesaur\Http\Message;

use Psr\Http\Message\UriInterface;

/**
 * PSR-7 UriInterface хэрэгжилт.
 *
 * Энэ класс нь URI-ийн (Uniform Resource Identifier)
 * дараах бүрэлдэхүүн хэсгүүдийг удирдах зориулалттай:
 *
 *  - Scheme (http, https)
 *  - User info (user:password)
 *  - Host (домен эсвэл IPv6)
 *  - Port
 *  - Path
 *  - Query
 *  - Fragment
 *
 * PSR-7 шаардлагын дагуу URI объект нь immutable тул
 * withXXX() setter-үүд нь үргэлж clone буцаана.
 */
class Uri implements UriInterface
{
    private string $_scheme = '';
    private string $_host = '';
    private ?int $_port = null;
    private string $_path = '';
    private string $_query = '';
    private string $_fragment = '';
    private string $_user = '';
    private string $_password = '';

    /**
     * URI-ийн scheme-г буцаана (http эсвэл https).
     *
     * @return string
     */
    public function getScheme(): string
    {
        return $this->_scheme;
    }
    
    /**
     * Scheme тохируулах (mutable setter).
     *
     * @param string $scheme http эсвэл https
     *
     * @throws \InvalidArgumentException Scheme буруу бол
     * @return void
     */
    public function setScheme(string $scheme)
    {
        $schm = \strtolower($scheme);
        if (!\in_array($schm, ['http', 'https'])) {
            throw new \InvalidArgumentException(__CLASS__ . ': Invalid HTTP scheme');
        }
        
        $this->_scheme = $schm;
    }

    /**
     * Authority хэсгийг (user@host:port) бүтнээр буцаана.
     *
     * @return string Authority string
     */
    public function getAuthority(): string
    {
        $userInfo = $this->getUserInfo();
        if ($userInfo != '') {
            $authority = "$userInfo@";
        } else {
            $authority = '';
        }
        
        $authority .= $this->getHost();
        
        $port = $this->getPort();
        if ($port !== null) {
            $authority .= ":$port";
        }

        return $authority;
    }

    /**
     * User info (username эсвэл username:password)-г буцаана.
     *
     * @return string
     */
    public function getUserInfo(): string
    {
        $info = $this->_user;
        if (!empty($this->_password)) {
            $info .= ":$this->_password";
        }
        return $info;
    }
    
    /**
     * User info-г тохируулах (mutable setter).
     *
     * @param string      $user     Username
     * @param string|null $password Password (optional)
     *
     * @return void
     */
    public function setUserInfo(string $user, ?string $password = null)
    {
        $this->_user = \rawurlencode($user);

        if (!empty($password)) {
            $this->_password = \rawurlencode($password);
        }
    }

    /**
     * Host-г буцаана (example.com)
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->_host;
    }

    /**
     * Host тохируулах (mutable setter).
     *
     * IPv6 хаяг бол [xxxx:xxxx] хэлбэрт хөрвүүлнэ.
     *
     * @param string $host
     *
     * @return void
     */
    public function setHost(string $host) 
    {
        if (\filter_var($host, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
            $host = "[$host]";
        }
        
        $this->_host = \strtolower($host);
    }
    
    /**
     * Port-г буцаана. 
     *
     * Default порт (80, 443) тохиолдолд null буцаана (PSR-7 requirement).
     *
     * @return int|null
     */
    public function getPort(): ?int
    {
        if (!empty($this->_port)) {
            $scheme = $this->getScheme();
            if (($scheme == 'https' && $this->_port == 443)
                || ($scheme == 'http' && ($this->_port == 80 || $this->_port == 8080))
            ) {
                return null;
            }
        }
        
        return $this->_port;
    }
    
    /**
     * Port тохируулах (mutable setter).
     *
     * @param int $port Valid range: 1–65535
     *
     * @throws \InvalidArgumentException
     * @return void
     */
    public function setPort(int $port)
    {
        if ($port < 1 || $port > 65535) {
            throw new \InvalidArgumentException(__CLASS__ . ': Invalid HTTP port');
        }
        
        $this->_port = $port;
    }

    /**
     * URI-ийн path хэсгийг буцаана.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->_path;
    }
    
    /**
     * Path тохируулах (mutable setter).
     *
     * @param string $path
     * @return void
     */
    public function setPath(string $path)
    {
        $this->_path = \rawurlencode($path);
    }

    /**
     * Query string-г буцаана (? дараах хэсэг, key=value&key2=value2).
     *
     * @return string
     */
    public function getQuery(): string
    {
        return $this->_query;
    }
    
    /**
     * Query тохируулах (mutable setter).
     *
     * @param string $query
     * @return void
     */
    public function setQuery(string $query)
    {
        $this->_query = \rawurlencode($query);
    }

    /**
     * Fragment-г буцаана (#info гэх мэт).
     *
     * @return string
     */
    public function getFragment(): string
    {
        return $this->_fragment;
    }
    
    /**
     * Fragment тохируулах (mutable setter).
     *
     * @param string $fragment
     * @return void
     */
    public function setFragment(string $fragment)
    {
        $this->_fragment = \rawurlencode($fragment);
    }

    /**
     * Immutable: Scheme-г өөрчилсөн шинэ URI instance буцаана.
     *
     * @inheritdoc
     */
    public function withScheme(string $scheme): UriInterface
    {
        $clone = clone $this;
        $clone->setScheme($scheme);
        return $clone;
    }

    /**
     * Immutable: User info-г өөрчилсөн шинэ URI instance буцаана.
     *
     * @inheritdoc
     */
    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $clone = clone $this;
        $clone->setUserInfo($user, $password);
        return $clone;
    }

    /**
     * Immutable: Host-г өөрчилсөн шинэ URI instance буцаана.
     *
     * @inheritdoc
     */
    public function withHost(string $host): UriInterface
    {
        $clone = clone $this;
        $clone->setHost($host);
        return $clone;
    }

    /**
     * Immutable: Port-г өөрчилсөн шинэ URI instance буцаана.
     *
     * @inheritdoc
     */
    public function withPort(?int $port): UriInterface
    {
        $clone = clone $this;
        $clone->setPort((int) $port);
        return $clone;
    }

    /**
     * Immutable: Path-г өөрчилсөн шинэ URI instance буцаана.
     *
     * @inheritdoc
     */
    public function withPath(string $path): UriInterface
    {
        $clone = clone $this;
        $clone->setPath($path);
        return $clone;
    }

    /**
     * Immutable: Query-г өөрчилсөн шинэ URI instance буцаана.
     *
     * @inheritdoc
     */
    public function withQuery(string $query): UriInterface
    {
        $clone = clone $this;
        $clone->setQuery($query);
        return $clone;
    }

    /**
     * Immutable: Fragment-г өөрчилсөн шинэ URI instance буцаана.
     *
     * @inheritdoc
     */
    public function withFragment(string $fragment): UriInterface
    {
        $clone = clone $this;
        $clone->setFragment($fragment);
        return $clone;
    }

    /**
     * URI-г бүрэн (scheme://authority/path?query#fragment) хэлбэрээр буцаана.
     *
     * Энэ нь URL-г хэвлэх эсвэл лог бичих үед ашиглагдана.
     *
     * @return string Бүрэн URI reference
     */
    public function __toString(): string
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $query = $this->getQuery();
        $fragment = $this->getFragment();
        
        $uri_reference = '';
        if ($scheme != '') {
            $uri_reference .= "$scheme:";
        }
        
        if ($authority != '') {
            $uri_reference .= "//$authority";
        }
        
        $uri_reference .= \rawurldecode($this->getPath());
        
        if ($query != '') {
            $uri_reference .= '?' . \rawurldecode($query);
        }
        
        if ($fragment != '') {
            $uri_reference .= '#' . \rawurldecode($fragment);
        }
        
        return $uri_reference;
    }
}
