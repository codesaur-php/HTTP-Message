<?php

namespace codesaur\Http\Message;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\UploadedFileInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    protected array $serverParams = [];
    
    protected array $cookies = [];
    
    protected array $attributes = [];

    protected array $parsedBody = [];
    
    protected array $uploadedFiles = [];
    
    protected ?array $queryParams = null;

    public function initFromGlobal()
    {
        $this->serverParams = $_SERVER;
        if (\function_exists('getallheaders')) {
            foreach (\getallheaders() as $key => $value) {
                $key = \strtoupper($key);
                $key = 'HTTP_' . \str_replace('-', '_', $key);
                if (!isset($this->serverParams[$key])) {
                    $this->serverParams[$key] = $value;
                }
            }
        }
        
        if (isset($this->serverParams['SERVER_PROTOCOL'])) {
            $this->protocolVersion = \str_replace('HTTP/', '', $this->serverParams['SERVER_PROTOCOL']);
        }
        
        $this->method = \strtoupper($this->serverParams['REQUEST_METHOD']);
        
        $this->cookies = $_COOKIE;
        
        $this->uri = new Uri();
        $https = $this->serverParams['HTTPS'] ?? 'off';
        $port = (int) $this->serverParams['SERVER_PORT'];
        if ((!empty($https) && \strtolower($https) != 'off')
            || $port == 443
        ) {
            $this->uri->setScheme('https');
        } else {
            $this->uri->setScheme('http');
        }
        $this->uri->setPort($port);
        $this->uri->setHost($this->serverParams['HTTP_HOST']);
        $this->setHeader('Host', $this->uri->getHost());

        $request_uri = \preg_replace('/\/+/', '\\1/', $this->serverParams['REQUEST_URI']);
        if (($pos = \strpos($request_uri, '?')) !== false) {
            $request_uri = \substr($request_uri, 0, $pos);
        }
        $this->requestTarget = \rtrim($request_uri, '/');
        $this->uri->setPath($this->requestTarget);
        
        if (!empty($this->serverParams['QUERY_STRING'])) {
            $query = $this->serverParams['QUERY_STRING'];
            $this->uri->setQuery($query);
            $this->requestTarget .= "?$query";
            \parse_str($query, $this->queryParams);
        }
        
        $this->uploadedFiles = $this->getNormalizedUploadedFiles($_FILES);
        
        if (($this->serverParams['CONTENT_LENGTH'] ?? 0) > 0) {
            if (empty($_POST)) {
                $input = \file_get_contents('php://input');
                if (empty($input)) {
                    $this->parsedBody = [];
                } else {
                    $decoded = \json_decode($input, true);
                    if ($decoded != null
                        && \json_last_error() == \JSON_ERROR_NONE
                    ) {
                        $this->parsedBody = $decoded;
                    } else {
                        $this->parseFormData($input);
                    }
                }
            } else {
                $this->parsedBody = $_POST;
            }
        }
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }
    
    public function setScriptTargetPath(string $uri_path_segment)
    {
        $this->serverParams['SCRIPT_TARGET_PATH'] = $uri_path_segment;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams(): array
    {
        return $this->cookies;
    }

    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->cookies = $cookies;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParams(): array
    {
        if (\is_array($this->queryParams)) {
            return $this->queryParams;
        }

        if (!$this->getUri() instanceof UriInterface) {
            return [];
        }
        
        $query = \rawurldecode($this->getUri()->getQuery());
        \parse_str($query, $this->queryParams);
        return $this->queryParams;
    }

    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $query): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->queryParams = $query;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * {@inheritdoc}
     */
    public function withParsedBody($data): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->parsedBody = (array) $data;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute(string $name, $default = null)
    {
        if (!isset($this->attributes[$name])) {
            return $default;
        }
        
        return $this->attributes[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutAttribute(string $name): ServerRequestInterface
    {
        $clone = clone $this;
        if (isset($clone->attributes[$name])) {
            unset($clone->attributes[$name]);
        }
        return $clone;
    }
    
    private function parseFormData(string $input)
    {
        $boundary = \substr($input, 0, \strpos($input, "\r\n") ?: 0);
        if (empty($boundary)) {
            \parse_str($input, $parsedBody);
            if (\count($parsedBody) != 1
                || \strlen(\key($parsedBody)) != \strlen($input)
            ) {
                $this->parsedBody = $parsedBody;
            }
            return;
        }
        
        $index = 0;
        $datas = [];
        $varNamesEncoded = '';
        $fileNamesEncoded = '';
        $tmp_dir = \ini_get('upload_tmp_dir') ?: \sys_get_temp_dir();
        $needle = '; filename=""';
        $length = \strlen($needle);
        
        $parts = \array_slice(\explode($boundary, $input), 1);
        foreach ($parts as $part) {
            if ($part == "--\r\n") {
                break;
            }

            $part = \ltrim($part, "\r\n");
            $raw_parts = \explode("\r\n\r\n", $part, 2);
            if (!isset($raw_parts[1])) {
                continue;
            }
            
            list($raw_headers_inline, $body) = $raw_parts;
            $raw_headers = \explode("\r\n", $raw_headers_inline);

            $headers = [];
            foreach ($raw_headers as $header) {
                list($content, $value) = \explode(':', $header);
                $headers[\strtolower($content)] = \ltrim($value, ' ');
            }

            if (!isset($headers['content-disposition'])) {
                continue;
            }
            
            $index++;
            
            $matches = [];
            \preg_match('/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/', $headers['content-disposition'], $matches);
            list(/*$content_header*/, /*$content_type*/, $name) = $matches;
            $encodedNameIndex = \urlencode($name) . '=' . $index;
            $data = \substr($body, 0, \strlen($body) - 2);
            if (!empty($matches[4]) && isset($headers['content-type'])) {
                $unique_tmp_name = \uniqid('php_') . '.tmp';
                $tmp_path = "$tmp_dir/$unique_tmp_name";
                $size = \file_put_contents($tmp_path, $data);
                if ($size === false) {
                    continue;
                }
                
                $data = new UploadedFile($tmp_path, $matches[4], $headers['content-type'], $size, \UPLOAD_ERR_OK);
                if ($fileNamesEncoded != '') {
                    $fileNamesEncoded .= '&';
                }
                $fileNamesEncoded .= $encodedNameIndex;
            } elseif (\substr($headers['content-disposition'], -$length) == $needle) {
                $data = new UploadedFile('', null, null, null, \UPLOAD_ERR_NO_FILE);
                if ($fileNamesEncoded != '') {
                    $fileNamesEncoded .= '&';
                }
                $fileNamesEncoded .= $encodedNameIndex;
            } else {
                if ($varNamesEncoded != '') {
                    $varNamesEncoded .= '&';
                }
                $varNamesEncoded .= $encodedNameIndex;
            }
            
            $datas[$index] = $data;
        }
        
        \parse_str($varNamesEncoded, $parsedBody);
        $this->arrayTreeLeafs($parsedBody, $datas);
        $this->parsedBody = $parsedBody;
        
        \parse_str($fileNamesEncoded, $uploadedFiles);
        $this->arrayTreeLeafs($uploadedFiles, $datas);
        $this->uploadedFiles = $uploadedFiles;
    }
    
    private function arrayTreeLeafs(array &$tree, array $leafs)
    {
        foreach ($tree as $key => &$value) {
            if (\is_array($value)) {
                $this->arrayTreeLeafs($value, $leafs);
            } else {
                $tree[$key] = $leafs[$value];
            }
        }
    }
    
    // Normalizing
    // Thank dakis for sharing excellent code
    // see reference => https://stackoverflow.com/questions/52027412/files-key-used-for-building-a-psr-7-uploaded-files-list
    
    /**
     * Normalize - if not already - the list of uploaded files as a tree of upload
     * metadata, with each leaf an instance of Psr\Http\Message\UploadedFileInterface.
     *
     *
     * IMPORTANT: For a correct normalization of the uploaded files list, the FIRST OCCURRENCE
     *            of the key "tmp_name" is checked against. See "POST method uploads" link.
     *            As soon as the key will be found in an item of the uploaded files list, it
     *            will be supposed that the array item to which it belongs is an array with
     *            a structure similar to the one saved in the global variable $_FILES when a
     *            standard file upload is executed.
     *
     * @link https://secure.php.net/manual/en/features.file-upload.post-method.php POST method uploads.
     * @link https://secure.php.net/manual/en/reserved.variables.files.php $_FILES.
     * @link https://tools.ietf.org/html/rfc1867 Form-based File Upload in HTML.
     * @link https://tools.ietf.org/html/rfc2854 The 'text/html' Media Type.
     *
     * @param array $uploadedFiles The list of uploaded files (normalized or not). Data MAY come from $_FILES or the message body.
     * @return array A tree of upload files in a normalized structure, with each leaf an instance of UploadedFileInterface.
     * @throws InvalidArgumentException An invalid structure of uploaded files list is provided.
     */
    private function getNormalizedUploadedFiles(array $uploadedFiles): array
    {
        $normalizedUploadedFiles = [];
        foreach ($uploadedFiles as $index => $item) {
            if (isset($item['tmp_name'])) {
                $normalizedUploadedFiles[$index] = $this->normalizeUploadedFile($item);
            } elseif (\is_array($item)) {
                $normalizedUploadedFiles[$index] = $this->getNormalizedUploadedFiles($item);
            } elseif ($item instanceof UploadedFileInterface) {
                $normalizedUploadedFiles[$index] = $item;
            } else {
                throw new \InvalidArgumentException('The structure of the uploaded files list is not valid.');
            }
        }
        
        return $normalizedUploadedFiles;
    }
    
    /**
     * Normalize the file upload item which contains the FIRST OCCURRENCE of the key "tmp_name".
     *
     * This method returns a tree structure, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface
     * or instance of Psr\Http\Message\UploadedFileInterface.
     *
     * Not part of PSR-17.
     *
     * @param array $item The file upload item.
     * @return array|UploadedFileInterface The file upload item as a tree structure, with each leaf an instance of UploadedFileInterface, or instance of Psr\Http\Message\UploadedFileInterface.
     * @throws InvalidArgumentException The value at the key "tmp_name" is empty.
     */
    private function normalizeUploadedFile(array $item)
    {
        $filename = $item['tmp_name'];
        if (\is_array($filename)) {
            if (empty($filename)) {
                throw new \InvalidArgumentException('The value of the key "tmp_name" in the uploaded files list must be a non-empty array.');
            }
            return $this->normalizeFileUploadTmpNameItem($filename, $item);
        }
        
        return new UploadedFile($filename, $item['name'] ?? null, $item['type'] ?? null, $item['size'] ?? null, $item['error'] ?? \UPLOAD_ERR_OK);
    }
    
    /**
     * Normalize the array assigned as value to the FIRST OCCURRENCE of the key "tmp_name" in a
     * file upload item of the uploaded files list. It is recursively iterated, in order to build
     * a tree structure, with each leaf an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * Not part of PSR-17.
     *
     * @param array $item The array assigned as value to the FIRST OCCURRENCE of the key "tmp_name".
     * @param array $currentElements An array holding the file upload key/value pairs of the current item.
     * @return array A tree structure, with each leaf an instance of UploadedFileInterface.
     * @throws InvalidArgumentException
     */
    private function normalizeFileUploadTmpNameItem(array $item, array $currentElements): array
    {
        $normalizedItem = [];
        foreach ($item as $key => $value) {
            if (\is_array($value)) {
                if (!isset($currentElements['size'][$key])
                    || !\is_array($currentElements['size'][$key])
                    || !isset($currentElements['error'][$key])
                    || !\is_array($currentElements['error'][$key])
                ) {
                    throw new \InvalidArgumentException('The structure of the items assigned to the keys "size" and "error" in the uploaded files list must be identical with the one of the  item assigned to the key "tmp_name". This restriction does not  apply to the leaf elements.');
                }
                
                $filename = $currentElements['tmp_name'][$key];
                $size = $currentElements['size'][$key];
                $error = $currentElements['error'][$key];
                $clientFilename = isset($currentElements['name'][$key]) && \is_array($currentElements['name'][$key]) ? $currentElements['name'][$key] : null;
                $clientMediaType = isset($currentElements['type'][$key]) && \is_array($currentElements['type'][$key]) ? $currentElements['type'][$key] : null;
                $normalizedItem[$key] = $this->normalizeFileUploadTmpNameItem($value, ['tmp_name' => $filename, 'size' => $size, 'error' => $error, 'name' => $clientFilename, 'type' => $clientMediaType]);
            } else {
                $normalizedItem[$key] = new UploadedFile($currentElements['tmp_name'][$key], $currentElements['name'][$key] ?? null, $currentElements['type'][$key] ?? null, $currentElements['size'][$key] ?? null, $currentElements['error'][$key] ?? \UPLOAD_ERR_OK);
            }
        }

        return $normalizedItem;
    }
}
