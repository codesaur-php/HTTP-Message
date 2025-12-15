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
     * User info нь URI string-д харагдах үед percent-encode хийгдэнэ.
     * Хэрэв user info аль хэдийн encoded байвал, decode хийж дараа нь encode хийнэ
     * (давхар encode-ийг зайлсхийх зорилгоор).
     *
     * @return string Authority string
     */
    public function getAuthority(): string
    {
        $userInfo = $this->getUserInfo();
        if ($userInfo != '') {
            // User info-г URI string-д харагдах үед encode хийнэ
            // user:password хэлбэрт байгаа тул user болон password-ийг тусад нь encode хийх хэрэгтэй
            $parts = \explode(':', $userInfo, 2);
            if (\count($parts) == 2) {
                // Decode хийж, дараа нь encode хийнэ (давхар encode-ийг зайлсхийх)
                $decodedUser = \rawurldecode($parts[0]);
                $decodedPassword = \rawurldecode($parts[1]);
                $encodedUser = \rawurlencode($decodedUser);
                $encodedPassword = \rawurlencode($decodedPassword);
                $authority = "$encodedUser:$encodedPassword@";
            } else {
                // Decode хийж, дараа нь encode хийнэ (давхар encode-ийг зайлсхийх)
                $decodedUser = \rawurldecode($userInfo);
                $encodedUser = \rawurlencode($decodedUser);
                $authority = "$encodedUser@";
            }
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
     * Анхаар: User info-г encode хийхгүй, хэрэглэгч өгсөн утгыг шууд хадгална.
     * PSR-7 стандартын дагуу user info нь аль хэдийн encoded эсвэл unencoded байж болно.
     * URI-г string хэлбэрт хөрвүүлэхдээ (getAuthority(), __toString()) шаардлагатай хэсгүүдийг encode хийнэ.
     *
     * @param string      $user     Username (encoded эсвэл unencoded)
     * @param string|null $password Password (optional, encoded эсвэл unencoded)
     *
     * @return void
     */
    public function setUserInfo(string $user, ?string $password = null)
    {
        $this->_user = $user;

        if (!empty($password)) {
            $this->_password = $password;
        } else {
            $this->_password = '';
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
     * Анхаар: Path-г encode хийхгүй, хэрэглэгч өгсөн утгыг шууд хадгална.
     * PSR-7 стандартын дагуу path нь аль хэдийн encoded эсвэл unencoded байж болно.
     * URI-г string хэлбэрт хөрвүүлэхдээ (__toString()) шаардлагатай хэсгүүдийг encode хийнэ.
     *
     * @param string $path URI path хэсэг (encoded эсвэл unencoded)
     * @return void
     */
    public function setPath(string $path)
    {
        $this->_path = $path;
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
     * Анхаар: Query string-г encode хийхгүй, хэрэглэгч өгсөн утгыг шууд хадгална.
     * PSR-7 стандартын дагуу query нь аль хэдийн encoded эсвэл unencoded байж болно.
     * URI-г string хэлбэрт хөрвүүлэхдээ (__toString()) шаардлагатай хэсгүүдийг encode хийнэ.
     *
     * @param string $query Query string (key=value&key2=value2 хэлбэр, encoded эсвэл unencoded)
     * @return void
     */
    public function setQuery(string $query)
    {
        $this->_query = $query;
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
     * Анхаар: Fragment-г encode хийхгүй, хэрэглэгч өгсөн утгыг шууд хадгална.
     * PSR-7 стандартын дагуу fragment нь аль хэдийн encoded эсвэл unencoded байж болно.
     * URI-г string хэлбэрт хөрвүүлэхдээ (__toString()) шаардлагатай хэсгүүдийг encode хийнэ.
     *
     * @param string $fragment URI fragment (# дараах хэсэг, encoded эсвэл unencoded)
     * @return void
     */
    public function setFragment(string $fragment)
    {
        $this->_fragment = $fragment;
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
     * Анхаар: Path, query, fragment нь хадгалсан утгаар нь шууд ашиглагдана.
     * PSR-7 стандартын дагуу эдгээр утгууд аль хэдийн percent-encoded байх ёстой.
     * Хэрэв хэрэглэгч unencoded утга өгсөн бол, URI зөв ажиллахгүй байж болно.
     *
     * @return string Бүрэн URI reference
     */
    public function __toString(): string
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $path = $this->getPath();
        $query = $this->getQuery();
        $fragment = $this->getFragment();
        
        $uri_reference = '';
        if ($scheme != '') {
            $uri_reference .= "$scheme:";
        }
        
        if ($authority != '') {
            $uri_reference .= "//$authority";
        }
        
        // Path нь хадгалсан утгаар нь шууд ашиглагдана (PSR-7 стандарт)
        $uri_reference .= $path;
        
        if ($query != '') {
            // Query string нь хадгалсан утгаар нь шууд ашиглагдана
            $uri_reference .= '?' . $query;
        }
        
        if ($fragment != '') {
            // Fragment нь хадгалсан утгаар нь шууд ашиглагдана
            $uri_reference .= '#' . $fragment;
        }
        
        return $uri_reference;
    }
}
