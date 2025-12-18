<?php

namespace codesaur\Http\Message;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * PSR-7 стандартын ServerRequest (серверийн талын HTTP хүсэлт) объектын бүрэн хэрэгжилт.
 *
 * Энэ класс нь веб серверээс ирсэн HTTP хүсэлтийн бүх мэдээллийг
 * (headers, cookies, URI, query, body, uploaded files, attributes, server params)
 * нэг цэгт цэгцтэйгээр агуулах бөгөөд immutable зарчмаар удирдана.
 *
 * `initFromGlobal()` нь PHP-ийн глобал хувьсагчдаас ($_SERVER, $_GET, $_POST…)
 * ServerRequest объект автоматаар угсарч авах зориулалттай.
 *
 * Дэмжинэ:
 *  - Cookies
 *  - Query parameters
 *  - Parsed body (JSON, x-www-form-urlencoded, multipart/form-data)
 *  - Uploaded files (гүехэн & олон түвшинтэй файл)
 *  - Server params
 *  - Custom attributes
 *
 * PSR-7 шаардлагын дагуу бүх setter функцүүд нь clone хийж immutable байна.
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * $_SERVER утгууд.
     *
     * @var array
     */
    protected array $serverParams = [];
    
    /**
     * Cookies (client-с ирсэн).
     *
     * @var array
     */
    protected array $cookies = [];
    
    /**
     * Custom attributes - middleware болон router-д голчлон ашиглагддаг.
     * хэрэглэгчийн дурын нэмэлт мэдээлэл байна.
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * Parsed body - JSON эсвэл form-urlencoded эсвэл multipart form-ийн body.
     *
     * @var array
     */
    protected array $parsedBody = [];
    
    /**
     * Uploaded files tree (PSR-7 UploadedFileInterface бүтэц).
     *
     * @var array
     */
    protected array $uploadedFiles = [];
    
    /**
     * Query parameters (lazy parse).
     *
     * @var array|null
     */
    protected ?array $queryParams = null;

    /**
     * PHP-ийн глобал хувьсагчдаас ServerRequest-ийг бүрэн угсарна.
     *
     * Доорх эх сурвалжаас мэдээлэл уншина:
     *  - $_SERVER → serverParams, протокол, method, host, port, uri, query
     *  - getallheaders() байвал → headers → serverParams дотор нэгтгэнэ
     *  - $_COOKIE → cookies
     *  - $_FILES → uploadedFiles (normalize хийж)
     *  - php://input / $_POST → parsedBody
     *
     * @return static Энэхүү ServerRequest-ийн өөрийн instance
     */
    public function initFromGlobal()
    {
        // SERVER PARAMS
        $this->serverParams = $_SERVER;
        
        // HEADERS (normalize: HTTP_HOST → SERVER['HTTP_HOST'])
        if (\function_exists('getallheaders')) {
            foreach (\getallheaders() as $key => $value) {
                $key = \strtoupper($key);
                $key = 'HTTP_' . \str_replace('-', '_', $key);
                if (!isset($this->serverParams[$key])) {
                    $this->serverParams[$key] = $value;
                }
            }
        }
        
        // PROTOCOL
        if (isset($this->serverParams['SERVER_PROTOCOL'])) {
            $this->protocolVersion = \str_replace('HTTP/', '', $this->serverParams['SERVER_PROTOCOL']);
        }
        
        // METHOD
        $this->method = \strtoupper($this->serverParams['REQUEST_METHOD']);
                
        // COOKIES
        $this->cookies = $_COOKIE;
        
        // BUILD URI
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

        // REQUEST_URI нь path, query string, fragment агуулж болно
        $request_uri = $this->serverParams['REQUEST_URI'] ?? '';
        // Fragment (#) салгах - REQUEST_URI-д fragment байж болно
        if (($fragmentPos = \strpos($request_uri, '#')) !== false) {
            $fragment = \substr($request_uri, $fragmentPos + 1);
            // Fragment нь PHP серверээс аль хэдийн percent-encoded байх ёстой
            $this->uri->setFragment($fragment);
            $request_uri = \substr($request_uri, 0, $fragmentPos);
        }
        // Query string (?) салгах - REQUEST_URI-д query string байж болно
        if (($queryPos = \strpos($request_uri, '?')) !== false) {
            $request_uri = \substr($request_uri, 0, $queryPos);
        }
        // Path-г normalize хийх (олон /-ийг нэг болгох)
        $path = \preg_replace('/\/+/', '/', $request_uri);
        $path = \rtrim($path, '/');
        if (empty($path)) {
            $path = '/';
        }
        
        // Path нь PHP серверээс аль хэдийн percent-encoded байх ёстой
        $this->uri->setPath($path);
        $this->requestTarget = $path;
        
        // QUERY STRING
        if (!empty($this->serverParams['QUERY_STRING'])) {
            $query = $this->serverParams['QUERY_STRING'];
            // QUERY_STRING нь PHP серверээс аль хэдийн percent-encoded байх ёстой
            $this->uri->setQuery($query);
            $this->requestTarget .= "?$query";
            \parse_str($query, $this->queryParams);
        }
        
        // Fragment-ийг request target-д нэмэх
        $fragment = $this->uri->getFragment();
        if ($fragment != '') {
            $this->requestTarget .= "#$fragment";
        }
        
        // UPLOADED FILES (normalize)
        $this->uploadedFiles = $this->getNormalizedUploadedFiles($_FILES);
        
        // PARSED BODY
        if (($this->serverParams['CONTENT_LENGTH'] ?? 0) > 0) {
            if (empty($_POST)) {
                // JSON эсвэл raw input
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
                // application/x-www-form-urlencoded
                $this->parsedBody = $_POST;
            }
        }
        
        return $this;
    }
    
    /**
     * Серверийн талаас ирсэн $_SERVER массивын утгуудыг буцаана.
     *
     * Энэ нь PHP серверийн орчин, request environment, headers болон
     * серверийн дотоод параметрүүдийг өөртөө агуулдаг.
     *
     * @return array Серверийн параметрийн жагсаалт
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }
    
    /**
     * Client талаас ирсэн бүх cookie утгуудыг буцаана.
     *
     * Энэ нь $_COOKIE-ийн хуулбар бөгөөд PSR-7 шаардлагын дагуу
     * immutable хэлбэрээр хадгалагдсан байдаг.
     *
     * @return array Cookie-ийн нэр/утгын массив
     */
    public function getCookieParams(): array
    {
        return $this->cookies;
    }

    /**
     * Шинэ cookie массивыг тохируулсан шинэ ServerRequest instance
     * (immutable clone) үүсгэнэ.
     *
     * @param array $cookies Cookie-ийн нэр/утгын жагсаалт
     *
     * @return ServerRequestInterface Иммутабл шинэ объект
     */
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->cookies = $cookies;

        return $clone;
    }

    /**
     * Query string (URI-ийн ? дараах хэсэг)-ийг задлан массив хэлбэрээр буцаана.
     *
     * Lazy-evaluation буюу анхны дуудалтын үед parse хийж,
     * дараагийн удаа кешлэгдсэн утгыг буцаана.
     *
     * Жишээ:  
     *   /product/view?id=10&lang=en  
     * → ['id' => '10', 'lang' => 'en']
     *
     * @return array Query параметрийн массив
     */
    public function getQueryParams(): array
    {
        if (\is_array($this->queryParams)) {
            return $this->queryParams;
        }

        if (!isset($this->uri)) {
            return [];
        }
        
        // Query string нь аль хэдийн percent-encoded байх ёстой
        // parse_str() нь автоматаар decode хийнэ
        $query = $this->getUri()->getQuery();
        \parse_str($query, $this->queryParams);
        return $this->queryParams;
    }

    /**
     * Query параметрийн массивыг шинэчлэн immutably clone буцаана.
     *
     * @param array $query Query string-ийг key/value массив хэлбэрээр
     *
     * @return ServerRequestInterface Шинэ request object
     */
    public function withQueryParams(array $query): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->queryParams = $query;
        return $clone;
    }

    /**
     * Upload хийгдсэн файлуудын PSR-7 UploadedFileInterface бүтэцтэй жагсаалтыг буцаана.
     *
     * Энэ жагсаалт нь олон түвшинтэй (nested) байж болно.
     *
     * @return array UploadedFileInterface instance-үүдээс бүрдэх мод бүтэц
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * Upload хийгдсэн файлуудын жагсаалтыг шинэчлэн immutably clone буцаана.
     *
     * @param array $uploadedFiles UploadedFileInterface instance-үүд
     *
     * @return ServerRequestInterface Шинэ объект
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;
        return $clone;
    }

    /**
     * Request body-г parse хийж гарсан үр дүнг буцаана.
     *
     * Дараах форматуудыг автоматаар задлана:
     *   - JSON
     *   - application/x-www-form-urlencoded
     *   - multipart/form-data (файлын бус хэсэг)
     *
     * @return mixed|null Parsed body (ихэвчлэн массив)
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * Parsed body-г шинэчлэн immutably clone буцаана.
     *
     * @param mixed $data Parsed body-д оноох шинэ утга (ихэвчлэн массив)
     *
     * @return ServerRequestInterface Шинэ request instance
     */
    public function withParsedBody($data): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->parsedBody = (array) $data;
        return $clone;
    }

    /**
     * Request-т хавсаргасан custom attribute-үүдийг массив хэлбэрээр буцаана.
     *
     * Attribute-уудыг middleware, router, framework-level логикт ашигладаг.
     *
     * @return array Attribute-ийн key/value жагсаалт
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Нэг attribute-ын утгыг нэрээр нь авч буцаана.
     *
     * @param string $name    Attribute-ийн нэр
     * @param mixed  $default Утга олдохгүй бол буцаах default утга
     *
     * @return mixed|null Attribute-ийн утга эсвэл default
     */
    public function getAttribute(string $name, $default = null)
    {
        if (!isset($this->attributes[$name])) {
            return $default;
        }
        
        return $this->attributes[$name];
    }

    /**
     * Нэг attribute-ыг нэмэн шинэ request instance буцаана (immutable).
     *
     * @param string $name  Attribute-ийн нэр
     * @param mixed  $value Утга
     *
     * @return ServerRequestInterface Шинэ request instance
     */
    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    /**
     * Нэг attribute-ыг устган immutably clone буцаана.
     *
     * @param string $name Устгах attribute-ийн нэр
     *
     * @return ServerRequestInterface Шинэ request instance
     */
    public function withoutAttribute(string $name): ServerRequestInterface
    {
        $clone = clone $this;
        if (isset($clone->attributes[$name])) {
            unset($clone->attributes[$name]);
        }
        return $clone;
    }
    
    /**
     * multipart/form-data body-г уншиж parse хийх дотоод функц.
     *
     * Алхамууд:
     *  - Boundary тодорхойлох
     *  - Boundary ашиглан хэсгүүдэд (parts) хуваах
     *  - Header болон body-г салгах
     *  - content-disposition-оос name болон filename-ийг олж авах
     *  - Файл хэсгүүдийг түр зуурын файл болгон диск дээр бичих
     *  - Текст хэсгүүдийг key=value хэлбэрээр хадгалах
     *  - parse_str() + arrayTreeLeafs() ашиглан олон түвшинтэй
     *    form нэршлийг бүрэн мод бүтэцтэй болгох
     *
     * Үр дүн:
     *  - $this->parsedBody - текстэн form field-үүд
     *  - $this->uploadedFiles - UploadedFile instance-үүдтэй мод бүтэц
     *
     * @param string $input Raw HTTP request body (multipart/form-data)
     *
     * @return void
     */
    private function parseFormData(string $input)
    {
        $boundary = \substr($input, 0, \strpos($input, "\r\n") ?: 0);
        
        // Boundary олдохгүй бол form-urlencoded гэж үзээд parse_str ашиглана
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
        
        // Boundary-гаар хэсэглэх
        $parts = \array_slice(\explode($boundary, $input), 1);
        foreach ($parts as $part) {
            // Эцсийн boundary (-boundary--)
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

            // Header-үүдийг боловсруулах
            $headers = [];
            foreach ($raw_headers as $header) {
                list($content, $value) = \explode(':', $header);
                $headers[\strtolower($content)] = \ltrim($value, ' ');
            }

            if (!isset($headers['content-disposition'])) {
                continue;
            }
            
            $index++;
            
            // name="" болон filename=""-ийг регуляр илэрхийллээр салгах
            $matches = [];
            \preg_match('/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/', $headers['content-disposition'], $matches);
            
            // $matches[2] = name, $matches[4] = filename (байвал)
            list(/*$content_header*/, /*$content_type*/, $name) = $matches;
            
            $encodedNameIndex = \urlencode($name) . '=' . $index;
            $data = \substr($body, 0, \strlen($body) - 2);
            
            // Файл upload хэсэг
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
                
            // Файл upload field боловч файл ирээгүй (filename="" кейс)                
            } elseif (\substr($headers['content-disposition'], -$length) == $needle) {
                $data = new UploadedFile('', null, null, null, \UPLOAD_ERR_NO_FILE);
                if ($fileNamesEncoded != '') {
                    $fileNamesEncoded .= '&';
                }
                $fileNamesEncoded .= $encodedNameIndex;
                
            // Энгийн текстэн form field
            } else {
                if ($varNamesEncoded != '') {
                    $varNamesEncoded .= '&';
                }
                $varNamesEncoded .= $encodedNameIndex;
            }
            
            // Ингэж индекс-утгыг datas массивт хадгална
            $datas[$index] = $data;
        }
        
        // TEXT FIELDS - varNamesEncoded → parsedBody
        \parse_str($varNamesEncoded, $parsedBody);
        $this->arrayTreeLeafs($parsedBody, $datas);
        $this->parsedBody = $parsedBody;
        
        // FILE FIELDS - fileNamesEncoded → uploadedFiles
        \parse_str($fileNamesEncoded, $uploadedFiles);
        $this->arrayTreeLeafs($uploadedFiles, $datas);
        $this->uploadedFiles = $uploadedFiles;
    }
    
    /**
     * Мод бүтэцтэй массивын навчууд (leaf)-ын индекс утгыг бодит
     * $leafs массивын утгаар солих recursive туслах функц.
     *
     * Жишээ:
     *  - $tree['user']['name'] = 1
     *  - $leafs[1] = "Bat"
     *  → үр дүнд: $tree['user']['name'] = "Bat"
     *
     * @param array $tree  Олон түвшинтэй мод бүтэцтэй массив
     * @param array $leafs Индекс → утга mapping бүхий массив
     *
     * @return void
     */
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
     * $_FILES буюу upload бүтэцийг PSR-7 UploadedFileInterface мод бүтэц рүү
     * нормалайз хийдэг туслах функц.
     *
     * - Шууд UploadedFileInterface instance ирвэл шууд ашиглана
     * - "tmp_name" key-тэй массив байвал normalizeUploadedFile() руу дамжуулна
     * - Олон түвшинтэй (nested) бүтэц байвал рекурсив дуудна
     *
     * @param array $uploadedFiles $_FILES эсвэл түүнтэй төстэй upload бүтэц
     *
     * @return array PSR-7-д нийцсэн normalized uploaded files мод
     *
     * @throws \InvalidArgumentException Буруу бүтэцтэй uploaded files дамжуулсан бол
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
     * "tmp_name" key агуулсан upload item-ийг PSR-7 UploadedFile эсвэл
     * түүнээс бүрдэх мод бүтцэд хөрвүүлнэ.
     *
     * - Хэрэв tmp_name нь массив бол олон файлын upload гэж үзээд
     *   normalizeFileUploadTmpNameItem() руу дамжуулна
     * - Энгийн string бол ганц файл → UploadedFile instance үүсгэнэ
     *
     * @param array $item $_FILES['field'] бүтцийн нэг элемент
     *
     * @return array|UploadedFileInterface
     *
     * @throws \InvalidArgumentException tmp_name массив хоосон байвал
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
     * "tmp_name" key-д массив оноогдсон тохиолдолд (олон файл upload),
     * тухайн массивыг рекурсивээр гүйж, навч бүр дээр UploadedFile instance
     * үүсгэж мод бүтэц болгон буцаана.
     *
     * @param array $item            tmp_name массив
     * @param array $currentElements size, error, name, type гэх мэт
     *                               upload item-ийн бусад талбарууд
     *
     * @return array UploadedFileInterface instance-үүдээс бүрдсэн мод бүтэц
     *
     * @throws \InvalidArgumentException size / error талбарын бүтэц tmp_name-тай
     *                                   тохирохгүй бол
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
