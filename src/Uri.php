<?php declare(strict_types=1);

namespace codesaur\Http\Message;

use Psr\Http\Message\UriInterface;

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
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return $this->_scheme;
    }
    
    public function setScheme(string $scheme)
    {
        $schm = strtolower($scheme);
        if (!in_array($schm, array('http', 'https'))) {
            throw new \InvalidArgumentException(__CLASS__ . ': Invalid HTTP scheme');
        }
        
        $this->_scheme = $schm;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
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
     * {@inheritdoc}
     */
    public function getUserInfo()
    {
        $info = $this->_user;
        if (!empty($this->_password)) {
            $info .= ":$this->_password";
        }
        return $info;
    }
    
    public function setUserInfo(string $user, ?string $password = null)
    {
        $this->_user = rawurlencode($user);

        if (empty($password)) {
            return;
        }

        $this->_password = rawurlencode($password);
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->_host;
    }

    public function setHost(string $host) 
    {
        if (filter_var($host, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
            $host = "[$host]";
        }
        
        $this->_host = strtolower($host);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getPort()
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
    
    public function setPort(int $port)
    {
        if ($port < 1 || $port > 65535) {
            throw new \InvalidArgumentException(__CLASS__ . ': Invalid HTTP port');
        }
        
        $this->_port = $port;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->_path;
    }
    
    public function setPath(string $path)
    {
        $this->_path = rawurlencode($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->_query;
    }
    
    public function setQuery(string $query)
    {
        $this->_query = rawurlencode($query);
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return $this->_fragment;
    }
    
    public function setFragment(string $fragment)
    {
        $this->_fragment = rawurlencode($fragment);
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme)
    {
        $clone = clone $this;
        $clone->setScheme($scheme);
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $password = null)
    {
        $clone = clone $this;
        $clone->setUserInfo($user, $password);
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host)
    {
        $clone = clone $this;
        $clone->setHost($host);
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        $clone = clone $this;
        $clone->setPort((int) $port);
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        $clone = clone $this;
        $clone->setPath($path);
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        $clone = clone $this;
        $clone->setQuery($query);
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        $clone = clone $this;
        $clone->setFragment($fragment);
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $query = $this->getQuery();
        $fragment = $this->getFragment();
        
        $uri_reference = '';
        if ($scheme !== '') {
            $uri_reference .= "$scheme:";
        }
        
        if ($authority !== '') {
            $uri_reference .= "//$authority";
        }
        
        $uri_reference .= rawurldecode($this->getPath());
        
        if ($query !== '') {
            $uri_reference .= '?' . rawurldecode($query);
        }
        
        if ($fragment !== '') {
            $uri_reference .= '#' . rawurldecode($fragment);
        }
        
        return $uri_reference;
    }
}
