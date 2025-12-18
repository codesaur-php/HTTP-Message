# 📚 API Documentation

Энэхүү баримт бичиг нь `codesaur/http-message` package-ийн бүх классуудын API-г PHPDoc-уудаас цуглуулж бүрдүүлсэн баримт бичиг юм.

---

## 📦 Namespace

Бүх классууд `codesaur\Http\Message` namespace-д байрлана.

---

## 🔷 Message (Abstract)

HTTP Message-ийн үндсэн abstract хэрэгжилт. PSR-7 стандартын MessageInterface-ийн бүх үндсэн функцүүдийг хэрэгжүүлэх суурь загвар.

### Онцлог
- HTTP протоколын дэмжигдэх хувилбаруудыг шалгана
- Header-үүдийг нэрээр нь case-insensitive байдлаар хадгална
- StreamInterface төрлийн message body ажиллуулна
- Бүх mutable өөрчлөлтүүд нь clone (immutable) зарчмаар буцаана

### Constants

```php
const HTTP_PROTOCOL_VERSIONS = ['1', '1.0', '1.1', '2', '2.0', '3', '3.0']
```

### Methods

#### `getProtocolVersion(): string`
HTTP протоколын одоогийн хувилбарыг буцаана.

#### `withProtocolVersion(string $version): MessageInterface`
HTTP протоколын хувилбарыг шинэчилсэн клон объект буцаана.

**Parameters:**
- `$version` (string): Дэмжигдэх протоколын хувилбар

**Throws:** `\InvalidArgumentException` - Хэрэв буруу хувилбар өгвөл

#### `getHeaders(): array<string,array>`
Message-ийн бүх header-үүдийг массив хэлбэрээр буцаана.

#### `hasHeader(string $name): bool`
Тухайн нэртэй header байгаа эсэхийг шалгана.

**Parameters:**
- `$name` (string): Header-ийн нэр

#### `getHeader(string $name): array`
Тухайн header-ийн бүх утгыг массив хэлбэртэй буцаана. Хэрэв байхгүй бол хоосон массив буцаана.

**Parameters:**
- `$name` (string): Header-ийн нэр

#### `getHeaderLine(string $name): string`
Header-ийн утгуудыг нэг мөрөнд (comma-separated) буцаана.

**Parameters:**
- `$name` (string): Header-ийн нэр

#### `withHeader(string $name, $value): MessageInterface`
Header-ийг overwrite хийсэн шинэ клон буцаана.

**Parameters:**
- `$name` (string): Header-ийн нэр
- `$value` (string|array): Header-ийн утга(ууд)

#### `withAddedHeader(string $name, $value): MessageInterface`
Header-т нэмэлт утга (append) хийсэн шинэ клон буцаана.

**Parameters:**
- `$name` (string): Header-ийн нэр
- `$value` (string|array): Нэмэх утга(ууд)

#### `withoutHeader(string $name): MessageInterface`
Тухайн header-ийг устгасан клон объект буцаана.

**Parameters:**
- `$name` (string): Устгах header-ийн нэр

#### `getBody(): StreamInterface`
Message body буюу StreamInterface объект буцаана. Body null бол (lazy initialization) хоосон php://temp stream автоматаар үүсгэнэ.

#### `withBody(StreamInterface $body): MessageInterface`
Шинэ body-г тохируулсан клон объект буцаана.

**Parameters:**
- `$body` (StreamInterface): Шинэ body stream

---

## 🔷 Request

PSR-7 стандартын HTTP Request объектын хэрэгжилт.

### Methods

#### `getRequestTarget(): string`
Request target-ийг буцаана. Custom утга тохируулсан бол тэрийг буцаана. Хоосон бол URI-ийн path + query + fragment-ийг ашиглан target үүсгэнэ.

#### `withRequestTarget(string $requestTarget): RequestInterface`
Request target-ийг шинэчилсэн клон объект буцаана.

**Parameters:**
- `$requestTarget` (string): Request target string

#### `getMethod(): string`
HTTP method-ийг буцаана (жишээ: GET, POST).

#### `withMethod(string $method): RequestInterface`
HTTP method-ийг шинэчилсэн клон буцаана.

**Parameters:**
- `$method` (string): Method (GET, POST, PUT, DELETE…)

**Throws:** `\InvalidArgumentException` - Хэрэв method буруу бол

#### `getUri(): UriInterface`
Request-ийн URI-г буцаана.

#### `withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface`
Шинэ URI-тай request клон буцаана.

**Parameters:**
- `$uri` (UriInterface): Шинэ URI
- `$preserveHost` (bool): true бол Host header-ийг хадгална

---

## 🔷 Response

PSR-7 стандартын HTTP Response (серверийн хариу) объектын хэрэгжилт.

### Constructor

```php
public function __construct()
```

Response үүсэх үед body-г Output buffering stream болгон тохируулна. Анхдагч body нь `Output` stream тул write() хийгдэх бүрт шууд browser/клиент рүү хэвлэгдэнэ.

### Methods

#### `getStatusCode(): int`
HTTP хариуны статус кодыг буцаана.

#### `withStatus(int $code, string $reasonPhrase = ''): ResponseInterface`
Шинэ статус код болон reason phrase тохируулах immutable setter.

**Parameters:**
- `$code` (int): RFC стандартын HTTP статус код
- `$reasonPhrase` (string): Custom текстэн тайлбар (optional)

**Throws:** `\InvalidArgumentException` - код integer биш эсвэл ReasonPhrase класст байхгүй статус код бол

#### `getReasonPhrase(): string`
Reason phrase (хариуны текстэн тайлбар)-г буцаана. Custom утга өгсөн бол тэрийг, үгүй бол ReasonPhrase::STATUS_xxx тогтмолоос стандарт утга буцаана.

---

## 🔷 NonBodyResponse

Body stream агуулаагүй HTTP хариу (Response)-ийн минимал хэрэгжилт.

### Үндсэн зорилго

Output buffer-тэй шууд `echo`, `print` эсвэл бусад PHP output функцүүд ашиглан browser/клиент рүү шууд хэвлэх үед зориулсан класс юм. Body stream огт агуулаагүй, учир нь контент нь output buffer-аас шууд browser руу дамжина.

### Response классын ялгаа

- **`Response`**: Body stream агуулдаг (default: `Output` stream). `$response->getBody()->write()` ашиглан body-д бичнэ.
- **`NonBodyResponse`**: Body stream огт байхгүй. Шууд `echo`, `print` эсвэл output buffer-аар хэвлэнэ.

### Хэрэглээ

```php
// Output buffer-тэй шууд хэвлэх
$response = new NonBodyResponse();
$response = $response->withStatus(200);
echo "Hello World"; // Шууд browser руу хэвлэгдэнэ

// Redirect
$redirect = (new NonBodyResponse())
    ->withStatus(302)
    ->withHeader('Location', '/new-page');
// Body байхгүй, зөвхөн headers илгээнэ
```

Ийм төрлийн хариу нь ихэвчлэн:
- Redirect (301, 302, 303, 307, 308)
- 204 No Content
- 304 Not Modified
- Output buffer-аар шууд хэвлэх үед
- эсвэл body шаардлагагүй бусад серверийн хариуд
ашиглагдана.

### Онцлог

- Message суурь классыг өргөтгөн headers болон protocol-той ажиллана
- Body stream огт тохируулаагүй (null). `getBody()` дуудахад `RuntimeException` шиднэ,
  учир нь энэ классын зорилго нь output buffer-тэй шууд browser руу хэвлэх тул
  body stream шаардлагагүй.
- Статус кодыг ReasonPhrase тогтмолуудаас баталгаажуулж шалгана
- Output buffer-тэй шууд echo, print хийх үед тохиромжтой

### Methods

#### `getStatusCode(): int`
HTTP хариуны статус кодыг буцаана.

#### `withStatus(int $code, string $reasonPhrase = ''): ResponseInterface`
Өгөгдсөн статус код болон reason phrase-тэй шинэ Response объект буцаана (PSR-7 immutable зарчим).

**Parameters:**
- `$code` (int): HTTP статус код
- `$reasonPhrase` (string): Нэмэлт тайлбар (заавал биш)

**Throws:** `\InvalidArgumentException` - Хэрэв статус код танигдаагүй бол

#### `getReasonPhrase(): string`
Хариуны reason phrase-г буцаана. Custom reason phrase өгсөн бол шууд тэрийг, хоосон бол ReasonPhrase классын статус кодын тогтмолоос автоматаар уншина.

#### `getBody(): StreamInterface`
Body stream буцаана. NonBodyResponse нь body stream агуулаагүй тул exception шиднэ.

**Throws:** `\RuntimeException` - NonBodyResponse нь body stream дэмжихгүй

**Анхаар:** NonBodyResponse-ийн зорилго нь output buffer-тэй шууд `echo`, `print` ашиглан browser руу хэвлэх тул body stream шаардлагагүй. Хэрэв body stream шаардлагатай бол `Response` классыг ашиглах хэрэгтэй.

---

## 🔷 ServerRequest

PSR-7 стандартын ServerRequest (серверийн талын HTTP хүсэлт) объектын бүрэн хэрэгжилт.

### Methods

#### `initFromGlobal(): static`
PHP-ийн глобал хувьсагчдаас ServerRequest-ийг бүрэн угсарна. Доорх эх сурвалжаас мэдээлэл уншина:
- `$_SERVER` → serverParams, протокол, method, host, port, uri, query
- `getallheaders()` байвал → headers → serverParams дотор нэгтгэнэ
- `$_COOKIE` → cookies
- `$_FILES` → uploadedFiles (normalize хийж)
- `php://input` / `$_POST` → parsedBody

**Returns:** Энэхүү ServerRequest-ийн өөрийн instance

#### `getServerParams(): array`
Серверийн талаас ирсэн `$_SERVER` массивын утгуудыг буцаана.

#### `getCookieParams(): array`
Client талаас ирсэн бүх cookie утгуудыг буцаана.

#### `withCookieParams(array $cookies): ServerRequestInterface`
Шинэ cookie массивыг тохируулсан шинэ ServerRequest instance (immutable clone) үүсгэнэ.

**Parameters:**
- `$cookies` (array): Cookie-ийн нэр/утгын жагсаалт

#### `getQueryParams(): array`
Query string (URI-ийн ? дараах хэсэг)-ийг задлан массив хэлбэрээр буцаана. Lazy-evaluation буюу анхны дуудалтын үед parse хийж, дараагийн удаа кешлэгдсэн утгыг буцаана.

#### `withQueryParams(array $query): ServerRequestInterface`
Query параметрийн массивыг шинэчлэн immutably clone буцаана.

**Parameters:**
- `$query` (array): Query string-ийг key/value массив хэлбэрээр

#### `getUploadedFiles(): array`
Upload хийгдсэн файлуудын PSR-7 UploadedFileInterface бүтэцтэй жагсаалтыг буцаана. Энэ жагсаалт нь олон түвшинтэй (nested) байж болно.

**Returns:** UploadedFileInterface instance-үүдээс бүрдэх мод бүтэц

#### `withUploadedFiles(array $uploadedFiles): ServerRequestInterface`
Upload хийгдсэн файлуудын жагсаалтыг шинэчлэн immutably clone буцаана.

**Parameters:**
- `$uploadedFiles` (array): UploadedFileInterface instance-үүд

#### `getParsedBody()`
Request body-г parse хийж гарсан үр дүнг буцаана. Дараах форматуудыг автоматаар задлана:
- JSON
- application/x-www-form-urlencoded
- multipart/form-data (файлын бус хэсэг)

**Returns:** Parsed body (ихэвчлэн массив)

#### `withParsedBody($data): ServerRequestInterface`
Parsed body-г шинэчлэн immutably clone буцаана.

**Parameters:**
- `$data` (mixed): Parsed body-д оноох шинэ утга (ихэвчлэн массив)

#### `getAttributes(): array`
Request-т хавсаргасан custom attribute-үүдийг массив хэлбэрээр буцаана. Attribute-уудыг middleware, router, framework-level логикт ашигладаг.

#### `getAttribute(string $name, $default = null)`
Нэг attribute-ын утгыг нэрээр нь авч буцаана.

**Parameters:**
- `$name` (string): Attribute-ийн нэр
- `$default` (mixed): Утга олдохгүй бол буцаах default утга

**Returns:** Attribute-ийн утга эсвэл default

#### `withAttribute(string $name, $value): ServerRequestInterface`
Нэг attribute-ыг нэмэн шинэ request instance буцаана (immutable).

**Parameters:**
- `$name` (string): Attribute-ийн нэр
- `$value` (mixed): Утга

#### `withoutAttribute(string $name): ServerRequestInterface`
Нэг attribute-ыг устган immutably clone буцаана.

**Parameters:**
- `$name` (string): Устгах attribute-ийн нэр

---

## 🔷 Uri

PSR-7 UriInterface хэрэгжилт. URI-ийн бүрэлдэхүүн хэсгүүдийг удирдах зориулалттай:
- Scheme (http, https)
- User info (user:password)
- Host (домен эсвэл IPv6)
- Port
- Path
- Query
- Fragment

### Methods

#### `getScheme(): string`
URI-ийн scheme-г буцаана (http эсвэл https).

#### `setScheme(string $scheme): void`
Scheme тохируулах (mutable setter).

**Parameters:**
- `$scheme` (string): http эсвэл https

**Throws:** `\InvalidArgumentException` - Scheme буруу бол

#### `getAuthority(): string`
Authority хэсгийг (user@host:port) бүтнээр буцаана.

#### `getUserInfo(): string`
User info (username эсвэл username:password)-г буцаана.

#### `setUserInfo(string $user, ?string $password = null): void`
User info-г тохируулах (mutable setter).

**Parameters:**
- `$user` (string): Username (encoded эсвэл unencoded)
- `$password` (string|null): Password (optional, encoded эсвэл unencoded)

#### `getHost(): string`
Host-г буцаана (example.com).

#### `setHost(string $host): void`
Host тохируулах (mutable setter). IPv6 хаяг бол [xxxx:xxxx] хэлбэрт хөрвүүлнэ.

**Parameters:**
- `$host` (string): Host

#### `getPort(): ?int`
Port-г буцаана. Default порт (80, 443) тохиолдолд null буцаана (PSR-7 requirement).

#### `setPort(int $port): void`
Port тохируулах (mutable setter).

**Parameters:**
- `$port` (int): Valid range: 1–65535

**Throws:** `\InvalidArgumentException` - Port буруу бол

#### `getPath(): string`
URI-ийн path хэсгийг буцаана.

#### `setPath(string $path): void`
Path тохируулах (mutable setter).

**Parameters:**
- `$path` (string): URI path хэсэг (encoded эсвэл unencoded)

#### `getQuery(): string`
Query string-г буцаана (? дараах хэсэг, key=value&key2=value2).

#### `setQuery(string $query): void`
Query тохируулах (mutable setter).

**Parameters:**
- `$query` (string): Query string (key=value&key2=value2 хэлбэр, encoded эсвэл unencoded)

#### `getFragment(): string`
Fragment-г буцаана (#info гэх мэт).

#### `setFragment(string $fragment): void`
Fragment тохируулах (mutable setter).

**Parameters:**
- `$fragment` (string): URI fragment (# дараах хэсэг, encoded эсвэл unencoded)

#### Immutable Methods

Бүх `with*()` method-ууд нь immutable тул шинэ URI instance буцаана:

- `withScheme(string $scheme): UriInterface`
- `withUserInfo(string $user, ?string $password = null): UriInterface`
- `withHost(string $host): UriInterface`
- `withPort(?int $port): UriInterface`
- `withPath(string $path): UriInterface`
- `withQuery(string $query): UriInterface`
- `withFragment(string $fragment): UriInterface`

#### `__toString(): string`
URI-г бүрэн (scheme://authority/path?query#fragment) хэлбэрээр буцаана.

---

## 🔷 Stream

PSR-7 StreamInterface хэрэгжилт - файл resource дээр суурилсан. PHP resource (fopen() буцаасан) дээр суурилсан StreamInterface хэрэгжилт юм. Request body-д ашиглагдана.

### Constructor

```php
public function __construct($resource)
```

**Parameters:**
- `$resource` (resource): PHP stream resource (fopen() буцаасан)

**Throws:** `\InvalidArgumentException` - Хэрэв $resource нь resource биш бол

### Methods

#### `__toString(): string`
Stream-ийн бүх контентыг string хэлбэрээр буцаана.

#### `close(): void`
Stream-ийн resource-г хааж, stream-г хаана.

#### `detach()`
Stream-ийн resource-г салгаж, stream-г хаана. Resource-г буцаана, гэхдээ stream-г дахин ашиглах боломжгүй болгоно.

**Returns:** resource|null - Stream resource эсвэл null (detached бол)

#### `getSize(): ?int`
Stream-ийн хэмжээг (bytes) буцаана.

**Returns:** Stream-ийн хэмжээ эсвэл null (detached бол)

#### `tell(): int`
Stream-ийн одоогийн байрлалыг (position) буцаана.

**Throws:** `\RuntimeException` - Stream detached бол эсвэл байрлал тодорхойлох боломжгүй бол

#### `eof(): bool`
Stream-ийн төгсгөлд хүрсэн эсэхийг шалгана (EOF - End Of File).

**Returns:** true бол EOF, false бол унших боломжтой

#### `isSeekable(): bool`
Stream seek хийх боломжтой эсэхийг шалгана.

**Returns:** true бол seekable, false бол биш

#### `seek(int $offset, int $whence = SEEK_SET): void`
Stream-ийн байрлалыг өөрчлөнө (seek).

**Parameters:**
- `$offset` (int): Шинэ байрлал (bytes)
- `$whence` (int): SEEK_SET (эхлэл), SEEK_CUR (одоогийн), SEEK_END (төгсгөл)

**Throws:** `\RuntimeException` - Stream seekable биш эсвэл seek хийх боломжгүй бол

#### `rewind(): void`
Stream-ийн байрлалыг эхлэл рүү буцаана (rewind).

**Throws:** `\RuntimeException` - Stream seekable биш бол

#### `isWritable(): bool`
Stream бичих боломжтой эсэхийг шалгана.

**Returns:** true бол writable, false бол биш

#### `write(string $string): int`
Stream-д мэдээлэл бичнэ.

**Parameters:**
- `$string` (string): Бичих string

**Returns:** Бичигдсэн тэмдэгтийн тоо

**Throws:** `\RuntimeException` - Stream writable биш эсвэл бичих боломжгүй бол

#### `isReadable(): bool`
Stream унших боломжтой эсэхийг шалгана.

**Returns:** true бол readable, false бол биш

#### `read(int $length): string`
Stream-аас мэдээлэл уншина.

**Parameters:**
- `$length` (int): Унших тэмдэгтийн тоо

**Returns:** Уншсан мэдээлэл

**Throws:** `\RuntimeException` - Stream readable биш эсвэл унших боломжгүй бол

#### `getContents(): string`
Stream-ийн үлдсэн бүх контентыг уншина.

**Returns:** Stream-ийн үлдсэн контент

**Throws:** `\RuntimeException` - Stream detached бол эсвэл унших боломжгүй бол

#### `getMetadata(?string $key = null)`
Stream-ийн metadata-г буцаана.

**Parameters:**
- `$key` (string|null): Metadata key (null бол бүх metadata)

**Returns:**
- array: Бүх metadata (key null бол)
- mixed: Тодорхой key-ийн утга
- null: Key олдохгүй эсвэл stream detached бол

---

## 🔷 UploadedFile

PSR-7 UploadedFileInterface хэрэгжилт. Upload хийгдсэн файлын metadata болон түр хадгалагдсан файлын зам (tmp_name)-ийг удирдах зориулалттай.

### Constructor

```php
public function __construct(string $tmp_name, ?string $name, ?string $type, ?int $size, int $error)
```

**Parameters:**
- `$tmp_name` (string): Түр хадгалагдсан файл (tmp path)
- `$name` (string|null): Клиент filename
- `$type` (string|null): MIME type
- `$size` (int|null): Файлын хэмжээ
- `$error` (int): PHP upload error код

### Methods

#### `getClientFilename(): ?string`
Клиентээс ирсэн эх filename-г буцаана.

**Returns:** Original client filename

#### `getClientMediaType(): ?string`
Клиентээс ирсэн MIME төрөл (жишээ: image/jpeg).

**Returns:** MIME төрөл

#### `getSize(): ?int`
Upload хийгдсэн файлын хэмжээ (байвал).

**Returns:** Файлын хэмжээ bytes-ээр

#### `getError(): int`
PHP upload error кодыг буцаана.

**Returns:** PHP UPLOAD_ERR_* тогтмолын аль нэг

#### `getStream(): StreamInterface`
Файлаас stream үүсгэх хэрэгжилт. Support хийгдээгүй тул exception шиднэ.

**Throws:** `\RuntimeException`

#### `moveTo(string $targetPath): void`
Upload хийгдсэн файлыг түр хавтасаас зорилтот байршил руу зөөнө.

**Parameters:**
- `$targetPath` (string): Зорилтот файлын абсолют эсвэл харьцангуй зам

**Throws:**
- `\InvalidArgumentException` - targetPath хоосон бол
- `\RuntimeException` - Файл байхгүй эсвэл аль хэдийн зөөгдсөн бол
- `\RuntimeException` - Upload error гарсан бол
- `\RuntimeException` - Файлыг зөөх эсвэл temp файлыг устгахад алдаа гарвал

#### `jsonSerialize(): mixed`
UploadedFile объектыг JSON руу serialize хийхэд ашиглагдах утгууд.

**Returns:** Объектын бүх property-г key/value хэлбэрээр буцаана

---

## 🔷 Output

Output stream – PHP-ийн output buffering-д суурилсан StreamInterface хэрэгжилт. HTTP хариуны body-г "шууд браузер руу хэвлэх" зарчмаар ажилладаг тусгай stream.

### Constructor

```php
public function __construct()
```

Output stream үүсэхэд output buffering автоматаар эхэлнэ.

### Methods

#### `getBuffer(): OutputBuffer`
OutputBuffer обьектыг буцаана.

#### `__toString(): string`
Stream-ийн бүх контентыг string хэлбэрээр буцаана.

#### `close(): void`
Output buffering-ийг цэвэрлэж хаана.

#### `detach()`
Stream-ийн ресурсыг салгах боломжгүй.

**Throws:** `\RuntimeException`

#### `getSize(): ?int`
Stream-ийн нийт хэмжээ (buffer length)-г буцаана.

#### `tell(): int`
Seekable биш stream тул үргэлж 0 буцаана.

#### `eof(): bool`
Output stream үргэлж EOF (унших боломжгүй) төлөвтэй байдаг.

**Returns:** true

#### `isSeekable(): bool`
Seek дэмждэггүй.

**Returns:** false

#### `seek(int $offset, int $whence = SEEK_SET): void`
Seek боломжгүй тул алдаа үүсгэнэ.

**Throws:** `\RuntimeException`

#### `rewind(): void`
Rewind боломжгүй тул алдаа үүсгэнэ.

**Throws:** `\RuntimeException`

#### `isWritable(): bool`
Энэ stream нь зөвхөн бичих боломжтой.

**Returns:** true

#### `write(string $string): int`
Бичсэн string-ийг шууд echo хийж output руу дамжуулна.

**Parameters:**
- `$string` (string): Бичих string

**Returns:** Бичигдсэн тэмдэгтийн тоо

#### `isReadable(): bool`
Унших боломжгүй stream.

**Returns:** false

#### `read(int $length): string`
Read дэмждэггүй тул алдаа үүсгэнэ.

**Throws:** `\RuntimeException`

#### `getContents(): string`
OutputBuffer-ийн одоогийн контентыг string хэлбэрээр буцаана.

#### `getMetadata(?string $key = null)`
Output stream-д metadata концепц байхгүй тул үргэлж null буцаана.

**Returns:** null

---

## 🔷 OutputBuffer

PHP output buffering-ийн wrapper класс. ob_start(), ob_flush(), ob_get_contents() зэрэг PHP-ийн output buffer функцуудыг илүү цэгцтэй, объект хэлбэрээр ашиглах боломж олгоно.

### Methods

#### `start(int $chunk_size = 0, int $flags = PHP_OUTPUT_HANDLER_STDFLAGS): void`
Output buffering эхлүүлнэ.

**Parameters:**
- `$chunk_size` (int): Buffer-ийн chunk хэмжээ (0 = буферлэх)
- `$flags` (int): Output handler flags (PHP default: PHP_OUTPUT_HANDLER_STDFLAGS)

#### `startCallback(callable $output_callback, $chunk_size = 0, int $flags = PHP_OUTPUT_HANDLER_STDFLAGS): void`
Callback-тэй output buffering эхлүүлнэ.

**Parameters:**
- `$output_callback` (callable): Buffer process хийх callback
- `$chunk_size` (int): Chunk хэмжээ
- `$flags` (int): Flags

#### `startCompress(): void`
Output buffering-ийг compress() функц ашиглан эхлүүлнэ. HTML-ийг whitespace багасгасан, шахсан хэлбэрт шилжүүлдэг.

#### `flush(): void`
Буферийн одоогийн контентыг гаргаж (flush) буцаан хоосолно.

#### `endClean(): void`
Буферийн контентыг устгаж буферийг хаана.

#### `endFlush(): void`
Буферийг flush хийж чацруулан хаана.

#### `getLength(): int|false`
Буферийн уртыг буцаана.

**Returns:** Буферийн хэмжээ, эсвэл false

#### `getContents(): string|null|false`
Буфер доторх контентыг буцаана.

**Returns:**
- string: буферийн контент
- null: буфер байхгүй үед
- false: алдаа

#### `compress($buffer): string`
HTML output-ийг шахах (whitespace compression).

**Parameters:**
- `$buffer` (string): HTML buffer

**Returns:** Шахагдсан HTML

---

## 🔷 ReasonPhrase

HTTP статус код бүрийн стандарт reason phrase (утга тайлбар)-уудыг агуулсан utility класс. PSR-7 болон HTTP/1.1, HTTP/2 RFC стандартуудад заасан нийтлэг статусын текстэн тайлбаруудыг тогтмол (constant) хэлбэрээр нэг дор төвлөрүүлсэн.

### Constants

Бүх HTTP статус кодуудын reason phrase-ууд:

- **Informational (1xx):** STATUS_100, STATUS_101, STATUS_102, STATUS_103
- **Successful (2xx):** STATUS_200, STATUS_201, STATUS_202, STATUS_203, STATUS_204, STATUS_205, STATUS_206, STATUS_207, STATUS_208, STATUS_226
- **Redirection (3xx):** STATUS_300, STATUS_301, STATUS_302, STATUS_303, STATUS_304, STATUS_305, STATUS_306, STATUS_307, STATUS_308
- **Client Errors (4xx):** STATUS_400, STATUS_401, STATUS_402, STATUS_403, STATUS_404, STATUS_405, STATUS_406, STATUS_407, STATUS_408, STATUS_409, STATUS_410, STATUS_411, STATUS_412, STATUS_413, STATUS_414, STATUS_415, STATUS_416, STATUS_417, STATUS_418, STATUS_421, STATUS_422, STATUS_423, STATUS_424, STATUS_425, STATUS_426, STATUS_428, STATUS_429, STATUS_431, STATUS_451
- **Server Errors (5xx):** STATUS_500, STATUS_501, STATUS_502, STATUS_503, STATUS_504, STATUS_505, STATUS_506, STATUS_507, STATUS_508, STATUS_510, STATUS_511

### Usage

```php
$reason = ReasonPhrase::STATUS_404; // "Not Found"
```

---

## 📝 PSR-7 Interface Compliance

Бүх классууд дараах PSR-7 interface-уудтай нийцдэг:

- `Psr\Http\Message\MessageInterface`
- `Psr\Http\Message\RequestInterface`
- `Psr\Http\Message\ResponseInterface`
- `Psr\Http\Message\UriInterface`
- `Psr\Http\Message\ServerRequestInterface`
- `Psr\Http\Message\StreamInterface`
- `Psr\Http\Message\UploadedFileInterface`

---

## 🔗 Холбоос

- [README.md](README.md) - Package-ийн ерөнхий тайлбар
- [REVIEW.md](REVIEW.md) - Package-ийн бүрэн review
