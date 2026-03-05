# codesaur/http-message

**Clean, minimal, object-oriented HTTP Message component (PSR-7)**

`codesaur/http-message` is a lightweight component that implements HTTP message components following PHP's PSR-7 standard, including **Request**, **Response**,
**ServerRequest**, **URI**, **Stream**, **UploadedFile**, **OutputBuffer**, and more.

---

## Features

- **PSR-7 MessageInterface, RequestInterface, ResponseInterface** full implementation
- `ServerRequest::initFromGlobal()` - advanced parser to create request from global environment
- `multipart/form-data` **full multipart parser** (RFC 7578 compliant)
- `UploadedFile` - converts PHP upload files to PSR-7 format
- `Output` - stream to manage response body via output buffering
- `Uri` - URI components including scheme, host, path, query, fragment
- Works identically in server and CLI environments
- 0 external dependencies (only PSR interfaces)
- Framework-agnostic, fully compatible with codesaur, Laravel, Symfony, Slim, and all other PHP frameworks

---

## Installation

```bash
composer require codesaur/http-message
```

---

## Structure

| File | Purpose |
|------|---------|
| `Message` | PSR-7 MessageInterface implementation (headers, protocol, body) |
| `Request` | PSR-7 RequestInterface |
| `Response` | PSR-7 ResponseInterface |
| `NonBodyResponse` | Response without body stream. Designed to work with output buffer, directly printing to browser using `echo`, `print` |
| `ServerRequest` | Advanced implementation that reconstructs request from global environment |
| `Uri` | PSR-7 UriInterface |
| `Stream` | PSR-7 StreamInterface implementation (based on PHP resource) |
| `UploadedFile` | Uploaded file metadata + moveTo() |
| `Output` | StreamInterface implementation (output buffering) |
| `OutputBuffer` | Buffer management including minify, compress, flush, endClean |
| `ReasonPhrase` | Text descriptions for status codes |

---

# Usage Examples

## 1. Creating ServerRequest (from global $_SERVER, $_POST, $_FILES, ...)

```php
use codesaur\Http\Message\ServerRequest;

$request = new ServerRequest();
$request->initFromGlobal();

// Query params
var_dump($request->getQueryParams());

// Uploaded files
var_dump($request->getUploadedFiles());
```

---

## 2. Writing text using Response

```php
use codesaur\Http\Message\Response;

$response = new Response();
$response = $response->withStatus(200);

$body = $response->getBody();
// Note: Response's default body is an output buffer, so
// each write() immediately prints to browser/client
$body->write("<h1>Hello from codesaur!</h1>");

```

---

## 3. Returning JSON response example

```php
use codesaur\Http\Message\Response;

$data = ['status' => 'success', 'message' => 'Hello world'];

$response = (new Response())
    ->withHeader('Content-Type', 'application/json');

// Note: Response's default body is an output buffer, so
// each write() immediately prints to browser/client
$response->getBody()->write(\json_encode($data));

```

---

## 4. Processing file upload

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

## 5. URI management example

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

## 6. Using Stream example

The `Stream` class is a PSR-7 `StreamInterface` implementation based on PHP resource. Used for request body.

```php
use codesaur\Http\Message\Stream;

// Create php://temp stream (in memory)
$resource = \fopen('php://temp', 'r+');
$stream = new Stream($resource);

// Write to stream
$stream->write('Hello, World!');

// Rewind stream position to beginning
$stream->rewind();

// Read from stream
$content = $stream->read(5); // "Hello"

// Read all stream contents
$allContent = $stream->getContents();

// Close stream
$stream->close();
```

**Note:** `Message::getBody()` returns a `Stream` instance (creates `php://temp` stream if body is not set).

---

# Internal Implementation Highlights

## Multipart/form-data Parser

`ServerRequest::parseFormData()` is a powerful multipart parser compliant with RFC 7578 that supports:

- Multi-level array uploads
- Multiple file inputs with same name
- Empty filename ("No file selected")
- JSON + Raw body + urlencoded body fallback
- Automatic conversion to `UploadedFile` instances

---

## Stream - PSR-7 StreamInterface Implementation

The `Stream` class is a PSR-7 `StreamInterface` implementation based on PHP resource:

- Based on PHP `fopen()` returned resource
- Supports readable, writable, seekable streams
- Works with all PHP streams including `php://temp`, `php://memory`, file streams
- Automatically used for request body (`Message::getBody()`)
- Stream management methods like `tell()`, `seek()`, `rewind()`, `eof()`

---

## Output Buffer - StreamInterface Implementation

`Output` and `OutputBuffer` manage response body as follows:

- Start output buffering
- flush / clean / endFlush
- automatic whitespace-minify (`compress()`)
- String-cast -> returns body content

---

## PSR-7 Compliance

All withXXX() setters are **immutable**, always returning a clone.
All message components comply with the following PSR-7 interfaces:

- `Psr\Http\Message\MessageInterface`
- `Psr\Http\Message\RequestInterface`
- `Psr\Http\Message\ResponseInterface`
- `Psr\Http\Message\UriInterface`
- `Psr\Http\Message\ServerRequestInterface`
- `Psr\Http\Message\StreamInterface`
- `Psr\Http\Message\UploadedFileInterface`

---

## Running Tests

This project is fully tested using PHPUnit. To run tests:

```bash
# Install Composer dependencies (PHPUnit, etc.)
composer install

# Run tests using Composer commands (Recommended)
composer test                   # Run all tests
composer test-coverage          # Run tests with coverage (HTML report)

# Or run PHPUnit directly
./vendor/bin/phpunit            # Run all tests

# Run with coverage (HTML report)
./vendor/bin/phpunit --coverage-html coverage/html

# Run with coverage (Text report)
./vendor/bin/phpunit --coverage-text

# Generate coverage XML (for CI/CD)
./vendor/bin/phpunit --coverage-clover coverage.xml

# Run specific test file
./vendor/bin/phpunit tests/MessageTest.php

# Run edge case tests
./vendor/bin/phpunit tests/EdgeCaseTest.php

# Run integration tests
./vendor/bin/phpunit tests/Integration/
```

**For Windows users:** Replace `vendor/bin/phpunit` with `vendor\bin\phpunit.bat`

### Code Coverage Report

After generating coverage report:
- **HTML report:** Open `coverage/html/index.html` file in browser
- **Text report:** Saved as text format in `coverage/coverage.txt` file
- **XML report:** `coverage.xml` file is suitable for CI/CD systems (Codecov, Coveralls)

### Test Structure

| Test File | Tests Class |
|-----------|-------------|
| `tests/MessageTest.php` | `Message` (abstract) |
| `tests/RequestTest.php` | `Request` |
| `tests/ResponseTest.php` | `Response` |
| `tests/NonBodyResponseTest.php` | `NonBodyResponse` |
| `tests/UriTest.php` | `Uri` |
| `tests/UploadedFileTest.php` | `UploadedFile` |
| `tests/OutputTest.php` | `Output` |
| `tests/OutputBufferTest.php` | `OutputBuffer` |
| `tests/EdgeCaseTest.php` | Edge case tests (boundary cases) |
| `tests/Integration/FullRequestResponseTest.php` | Integration tests (all components together) |

---

## CI/CD (GitHub Actions)

This project uses GitHub Actions for automated CI/CD:

- **Tests on multiple PHP versions**: PHP 8.2, 8.3, 8.4
- **Multi-platform support**: Ubuntu and Windows
- **Automatic test execution**: On push and Pull Request
- **Code coverage**: Automatically sent to Codecov

---

## Documentation

- [API](api.md) - API documentation for all classes (automatically generated from PHPDoc using Cursor AI)
- [REVIEW](review.md) - Complete package review (code quality, architecture, usage possibilities - generated using Cursor AI)

---

## PHPDoc and Code Quality

- Complete PHPDoc documentation for all classes, methods, and properties
- All interfaces fully implemented according to PSR-7 standard
- Immutable principle followed in all setters
- Complete exception handling and validation

---

## License

This project is licensed under the MIT License.

---

## Author

Narankhuu
https://github.com/codesaur
