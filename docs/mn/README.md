# codesaur/http-message

**Цэвэр, минимал, объект хандалтат бүтэцтэй HTTP Message компонент (PSR-7)**

`codesaur/http-message` нь PHP-ийн PSR-7 стандартын дагуу **Request**, **Response**,
**ServerRequest**, **URI**, **Stream**, **UploadedFile**, **OutputBuffer** зэрэг HTTP
мессежийн бүрэлдэхүүнүүдийг хэрэгжүүлсэн бага жинтэй компонент юм.

---

## Онцлог

- **PSR-7 MessageInterface, RequestInterface, ResponseInterface** бүрэн хэрэгжилт
- `ServerRequest::initFromGlobal()` - глобал орчноос request үүсгэх advanced parser
- `multipart/form-data` **бүрэн multipart parser** (RFC 7578 дагуу)
- `UploadedFile` - PHP upload файлыг PSR-7 хэлбэрт хөрвүүлнэ
- `Output` - response body-г output buffering-аар удирдах stream
- `Uri` - scheme, host, path, query, fragment зэрэг URI бүрэлдэхүүн
- Сервер болон CLI орчинд адил ажиллана
- 0 external dependency (зөвхөн PSR interface-ууд)
- Framework-agnostic тул codesaur, Laravel, Symfony, Slim болон бусад бүх PHP framework-тэй бүрэн нийцтэй

---

## Суурилуулалт

```bash
composer require codesaur/http-message
```

---

## Бүтэц

| Файл | Үүрэг |
|------|-------|
| `Message` | PSR-7 MessageInterface хэрэгжилт (headers, protocol, body) |
| `Request` | PSR-7 RequestInterface |
| `Response` | PSR-7 ResponseInterface |
| `NonBodyResponse` | Body stream агуулаагүй response. Output buffer-тэй ажиллан шууд `echo`, `print` ашиглан browser руу хэвлэх үед зориулсан |
| `ServerRequest` | Глобал орчноос request сэргээдэг advanced implementation |
| `Uri` | PSR-7 UriInterface |
| `Stream` | PSR-7 StreamInterface хэрэгжилт (PHP resource дээр суурилсан) |
| `UploadedFile` | Upload хийгдсэн файлын metadata + moveTo() |
| `Output` | StreamInterface хэрэгжилт (output buffering) |
| `OutputBuffer` | Minify, compress, flush, endClean зэрэг буфер удирдлага |
| `ReasonPhrase` | Статус кодын текстэн тайлбарууд |

---

# Ашиглах жишээ

## 1. ServerRequest үүсгэх (глобал $_SERVER, $_POST, $_FILES, ...)

```php
use codesaur\Http\Message\ServerRequest;

$request = new ServerRequest();
$request->initFromGlobal();

// Query params
var_dump($request->getQueryParams());

// Uploaded files
var_dump($request->getUploadedFiles());

// PSR-7 headers уншиж ашиглах
$contentType = $request->getHeaderLine('Content-Type');
$csrfToken = $request->getHeaderLine('X-CSRF-TOKEN');
$accept = $request->getHeaderLine('Accept');

if ($request->hasHeader('Authorization')) {
    $auth = $request->getHeaderLine('Authorization');
}
```

---

## 2. Response ашиглан текст бичих

```php
use codesaur\Http\Message\Response;

$response = new Response();
$response = $response->withStatus(200);

$body = $response->getBody();
// Анхаар: Response-ийн default body нь output buffer тул
// write() хийгдэх бүрт шууд browser/клиент рүү хэвлэгдэнэ
$body->write("<h1>Hello from codesaur!</h1>");

```

---

## 3. JSON response буцаах жишээ

```php
use codesaur\Http\Message\Response;

$data = ['status' => 'success', 'message' => 'Hello world'];

$response = (new Response())
    ->withHeader('Content-Type', 'application/json');

// Анхаар: Response-ийн default body нь output buffer тул
// write() хийгдэх бүрт шууд browser/клиент рүү хэвлэгдэнэ
$response->getBody()->write(\json_encode($data));

```

---

## 4. File upload боловсруулах

```php
use codesaur\Http\Message\ServerRequest;

$request = (new ServerRequest())->initFromGlobal();
$files = $request->getUploadedFiles();

$avatar = $files['avatar'] ?? null;

if ($avatar) {
    $avatar->moveTo(__DIR__ . '/uploads/' . $avatar->getClientFilename());
}
```

---

## 5. URI удирдах жишээ

```php
use codesaur\Http\Message\Uri;

$uri = (new Uri())
    ->withScheme('https')
    ->withHost('example.com')
    ->withPath('/user/profile')
    ->withQuery('id=7');

echo (string) $uri;
// https://example.com/user/profile?id=7
```

---

## 6. Stream ашиглах жишээ

`Stream` класс нь PSR-7 `StreamInterface` хэрэгжилт бөгөөд PHP resource дээр суурилсан. Request body-д ашиглагдана.

```php
use codesaur\Http\Message\Stream;

// php://temp stream үүсгэх (memory дээр)
$resource = \fopen('php://temp', 'r+');
$stream = new Stream($resource);

// Stream-д бичих
$stream->write('Hello, World!');

// Stream-ийн байрлалыг эхлэл рүү буцаах
$stream->rewind();

// Stream-аас унших
$content = $stream->read(5); // "Hello"

// Stream-ийн бүх контентыг унших
$allContent = $stream->getContents();

// Stream хаах
$stream->close();
```

**Анхаар:** `Message::getBody()` нь `Stream` instance буцаана (хэрэв body тохируулаагүй бол `php://temp` stream үүсгэнэ).

---

# Дотоод ажиллагааны онцлох хэсгүүд

## Multipart/form-data Parser

`ServerRequest::parseFormData()` нь RFC 7578-д нийцсэн хүчирхэг multipart parser бөгөөд:

- Олон түвшинтэй массив upload
- Нэг нэртэй олон file input
- Хоосон filename ("No file selected")
- JSON + Raw body + urlencoded body fallback
- `UploadedFile` instance руу автоматаар хөрвүүлэлт

зэрэг бүгдийг дэмжинэ.

---

## Stream - PSR-7 StreamInterface хэрэгжилт

`Stream` класс нь PHP resource дээр суурилсан PSR-7 `StreamInterface` хэрэгжилт юм:

- PHP `fopen()` буцаасан resource-д суурилсан
- Readable, writable, seekable stream-үүдийг дэмжинэ
- `php://temp`, `php://memory`, файл stream зэрэг бүх PHP stream-үүдтэй ажиллана
- Request body-д автоматаар ашиглагдана (`Message::getBody()`)
- `tell()`, `seek()`, `rewind()`, `eof()` зэрэг stream удирдлагын method-ууд

---

## Output Buffer - StreamInterface хэрэгжилт

`Output` болон `OutputBuffer` нь response body-г дараах байдлаар удирддаг:

- output buffering эхлүүлэх
- flush / clean / endFlush
- автомат whitespace-minify (`compress()`)
- String-cast -> body контентыг буцаана

---

## PSR-7 нийцтэй байдал

Бүх withXXX() setter-үүд **immutable**, үргэлж clone буцаана.
Бүх мессежийн компонентууд PSR-7-ийн дараах interface-уудтай нийцдэг:

- `Psr\Http\Message\MessageInterface`
- `Psr\Http\Message\RequestInterface`
- `Psr\Http\Message\ResponseInterface`
- `Psr\Http\Message\UriInterface`
- `Psr\Http\Message\ServerRequestInterface`
- `Psr\Http\Message\StreamInterface`
- `Psr\Http\Message\UploadedFileInterface`

---

## Тест ажиллуулах

Энэ төсөл PHPUnit ашиглан бүрэн тест хийгдсэн. Тест ажиллуулах:

```bash
# Composer dependencies суулгах (PHPUnit зэрэг)
composer install

# Composer командууд ашиглан тест ажиллуулах (Зөвлөмж)
composer test                   # Бүх тест ажиллуулах
composer test-coverage          # Coverage-тэй тест ажиллуулах (HTML report)

# Эсвэл PHPUnit-ийг шууд ажиллуулах
./vendor/bin/phpunit            # Бүх тест ажиллуулах

# Coverage-тэй ажиллуулах (HTML report)
./vendor/bin/phpunit --coverage-html coverage/html

# Coverage-тэй ажиллуулах (Text report)
./vendor/bin/phpunit --coverage-text

# Coverage XML үүсгэх (CI/CD-д ашиглах)
./vendor/bin/phpunit --coverage-clover coverage.xml

# Тодорхой тест файл ажиллуулах
./vendor/bin/phpunit tests/MessageTest.php

# Edge case тестүүд ажиллуулах
./vendor/bin/phpunit tests/EdgeCaseTest.php

# Integration тестүүд ажиллуулах
./vendor/bin/phpunit tests/Integration/
```

**Windows хэрэглэгчид:** `vendor/bin/phpunit`-ийг `vendor\bin\phpunit.bat` гэж солино

### Code Coverage Report

Coverage report үүсгэсний дараа:
- **HTML report:** `coverage/html/index.html` файлыг browser-оор нээж харах
- **Text report:** `coverage/coverage.txt` файлд текстэн хэлбэрээр хадгалагдана
- **XML report:** `coverage.xml` файл нь CI/CD системд (Codecov, Coveralls) ашиглахад тохиромжтой

### Тест бүтэц

| Тест файл | Тестлэх класс |
|-----------|---------------|
| `tests/MessageTest.php` | `Message` (abstract) |
| `tests/RequestTest.php` | `Request` |
| `tests/ResponseTest.php` | `Response` |
| `tests/NonBodyResponseTest.php` | `NonBodyResponse` |
| `tests/UriTest.php` | `Uri` |
| `tests/UploadedFileTest.php` | `UploadedFile` |
| `tests/OutputTest.php` | `Output` |
| `tests/OutputBufferTest.php` | `OutputBuffer` |
| `tests/EdgeCaseTest.php` | Edge case тестүүд (хязгаарын тохиолдлууд) |
| `tests/Integration/FullRequestResponseTest.php` | Integration тестүүд (бүх компонентууд хамтдаа) |

---

## CI/CD (GitHub Actions)

Энэ төсөл GitHub Actions ашиглан автоматаар CI/CD хийгддэг:

- **Олон PHP хувилбар дээр тест**: PHP 8.2, 8.3, 8.4
- **Олон платформ дэмжлэг**: Ubuntu болон Windows
- **Автомат тест ажиллуулалт**: Push болон Pull Request үед
- **Code coverage**: Codecov руу автоматаар илгээгддэг

---

## Баримт бичиг

- [API](api.md) - Бүх классуудын API documentation (PHPDoc-уудаас Cursor AI ашиглан автоматаар үүсгэсэн)
- [REVIEW](review.md) - Package-ийн бүрэн review (код чанар, архитектур, ашиглалтын боломж - Cursor AI ашиглан үүсгэсэн)

---

## PHPDoc ба код чанар

- Бүх класс, метод, property-д бүрэн PHPDoc тайлбар бичигдсэн
- PSR-7 стандартын дагуу бүх interface-үүд бүрэн хэрэгжсэн
- Immutable зарчмыг бүх setter-үүдэд мөрдсөн
- Exception handling болон validation бүрэн хийгдсэн

---

## Лиценз

Энэ төсөл MIT лицензтэй.

---

## Зохиогч

**Narankhuu**
codesaur@gmail.com
https://github.com/codesaur
