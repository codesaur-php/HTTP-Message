# 📚 API Documentation

This documentation is compiled from PHPDoc comments for all classes in the `codesaur/http-message` package.

---

## 📦 Namespace

All classes are located in the `codesaur\Http\Message` namespace.

---

## 🔷 Message (Abstract)

Base abstract implementation of HTTP Message. Base template for implementing all core functions of PSR-7 standard MessageInterface.

### Features
- Validates supported HTTP protocol versions
- Stores headers by name in case-insensitive manner
- Manages StreamInterface type message body
- All mutable changes return clone (immutable principle)

### Constants

```php
const HTTP_PROTOCOL_VERSIONS = ['1', '1.0', '1.1', '2', '2.0', '3', '3.0']
```

### Methods

#### `getProtocolVersion(): string`
Returns the current HTTP protocol version.

#### `withProtocolVersion(string $version): MessageInterface`
Returns a cloned object with updated HTTP protocol version.

**Parameters:**
- `$version` (string): Supported protocol version

**Throws:** `\InvalidArgumentException` - If invalid version is provided

#### `getHeaders(): array<string,array>`
Returns all message headers as an array.

#### `hasHeader(string $name): bool`
Checks if a header with the given name exists.

**Parameters:**
- `$name` (string): Header name

#### `getHeader(string $name): array`
Returns all values of the specified header as an array. Returns empty array if not found.

**Parameters:**
- `$name` (string): Header name

#### `getHeaderLine(string $name): string`
Returns header values in a single line (comma-separated).

**Parameters:**
- `$name` (string): Header name

#### `withHeader(string $name, $value): MessageInterface`
Returns a new clone with overwritten header.

**Parameters:**
- `$name` (string): Header name
- `$value` (string|array): Header value(s)

#### `withAddedHeader(string $name, $value): MessageInterface`
Returns a new clone with appended header value.

**Parameters:**
- `$name` (string): Header name
- `$value` (string|array): Value(s) to add

#### `withoutHeader(string $name): MessageInterface`
Returns a cloned object with the specified header removed.

**Parameters:**
- `$name` (string): Name of header to remove

#### `getBody(): StreamInterface`
Returns message body or StreamInterface object. If body is null (lazy initialization), automatically creates empty php://temp stream.

#### `withBody(StreamInterface $body): MessageInterface`
Returns a cloned object with new body set.

**Parameters:**
- `$body` (StreamInterface): New body stream

---

## 🔷 Request

PSR-7 standard HTTP Request object implementation.

### Methods

#### `getRequestTarget(): string`
Returns the request target. Returns custom value if set. If empty, creates target using URI's path + query + fragment.

#### `withRequestTarget(string $requestTarget): RequestInterface`
Returns a cloned object with updated request target.

**Parameters:**
- `$requestTarget` (string): Request target string

#### `getMethod(): string`
Returns HTTP method (e.g., GET, POST).

#### `withMethod(string $method): RequestInterface`
Returns a clone with updated HTTP method.

**Parameters:**
- `$method` (string): Method (GET, POST, PUT, DELETE…)

**Throws:** `\InvalidArgumentException` - If method is invalid

#### `getUri(): UriInterface`
Returns the request URI.

#### `withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface`
Returns a request clone with new URI.

**Parameters:**
- `$uri` (UriInterface): New URI
- `$preserveHost` (bool): If true, preserves Host header

---

## 🔷 Response

PSR-7 standard HTTP Response (server response) object implementation.

### Constructor

```php
public function __construct()
```

When Response is created, body is set as Output buffering stream. Default body is `Output` stream, so each write() immediately prints to browser/client.

### Methods

#### `getStatusCode(): int`
Returns HTTP response status code.

#### `withStatus(int $code, string $reasonPhrase = ''): ResponseInterface`
Immutable setter to set new status code and reason phrase.

**Parameters:**
- `$code` (int): RFC standard HTTP status code
- `$reasonPhrase` (string): Custom text description (optional)

**Throws:** `\InvalidArgumentException` - If code is not integer or status code doesn't exist in ReasonPhrase class

#### `getReasonPhrase(): string`
Returns reason phrase (response text description). Returns custom value if provided, otherwise returns standard value from ReasonPhrase::STATUS_xxx constants.

---

## 🔷 NonBodyResponse

Minimal implementation of HTTP response (Response) without body stream.

### Main Purpose

This class is designed for directly printing to browser/client using `echo`, `print`, or other PHP output functions with output buffer. Body stream is not included at all, as content is directly passed from output buffer to browser.

### Difference from Response Class

- **`Response`**: Contains body stream (default: `Output` stream). Write to body using `$response->getBody()->write()`.
- **`NonBodyResponse`**: No body stream at all. Directly print using `echo`, `print`, or via output buffer.

### Usage

```php
// Direct printing with output buffer
$response = new NonBodyResponse();
$response = $response->withStatus(200);
echo "Hello World"; // Directly printed to browser

// Redirect
$redirect = (new NonBodyResponse())
    ->withStatus(302)
    ->withHeader('Location', '/new-page');
// No body, only headers sent
```

This type of response is commonly used for:
- Redirect (301, 302, 303, 307, 308)
- 204 No Content
- 304 Not Modified
- When directly printing via output buffer
- Or other server responses where body is not needed

### Features

- Extends Message base class to work with headers and protocol
- Body stream is not set at all (null). Throws `RuntimeException` when `getBody()` is called,
  as this class's purpose is to directly print to browser with output buffer, so
  body stream is not needed.
- Validates status codes from ReasonPhrase constants
- Suitable for direct echo, print with output buffer

### Methods

#### `getStatusCode(): int`
Returns HTTP response status code.

#### `withStatus(int $code, string $reasonPhrase = ''): ResponseInterface`
Returns a new Response object with given status code and reason phrase (PSR-7 immutable principle).

**Parameters:**
- `$code` (int): HTTP status code
- `$reasonPhrase` (string): Additional description (optional)

**Throws:** `\InvalidArgumentException` - If status code is not recognized

#### `getReasonPhrase(): string`
Returns response reason phrase. Returns custom reason phrase if provided, otherwise automatically reads from ReasonPhrase class status code constants.

#### `getBody(): StreamInterface`
Returns body stream. NonBodyResponse doesn't contain body stream, so throws exception.

**Throws:** `\RuntimeException` - NonBodyResponse doesn't support body stream

**Note:** NonBodyResponse's purpose is to directly print to browser using `echo`, `print` with output buffer, so body stream is not needed. If body stream is needed, use `Response` class.

---

## 🔷 ServerRequest

Full implementation of PSR-7 ServerRequest (server-side HTTP request) object.

### Methods

#### `initFromGlobal(): static`
Fully constructs ServerRequest from PHP global variables. Reads information from the following sources:
- `$_SERVER` → serverParams, protocol, method, host, port, uri, query
- `getallheaders()` if available → headers → integrated into serverParams
- `$_COOKIE` → cookies
- `$_FILES` → uploadedFiles (normalized)
- `php://input` / `$_POST` → parsedBody

**Returns:** This ServerRequest's own instance

#### `getServerParams(): array`
Returns values from server-side `$_SERVER` array.

#### `getCookieParams(): array`
Returns all cookie values from client side.

#### `withCookieParams(array $cookies): ServerRequestInterface`
Creates a new ServerRequest instance (immutable clone) with new cookie array set.

**Parameters:**
- `$cookies` (array): Cookie name/value list

#### `getQueryParams(): array`
Parses query string (part after ? in URI) and returns as array. Lazy-evaluation, parses on first call, returns cached value on subsequent calls.

#### `withQueryParams(array $query): ServerRequestInterface`
Returns immutably cloned object with updated query parameter array.

**Parameters:**
- `$query` (array): Query string as key/value array

#### `getUploadedFiles(): array`
Returns list of uploaded files in PSR-7 UploadedFileInterface structure. This list can be multi-level (nested).

**Returns:** Tree structure consisting of UploadedFileInterface instances

#### `withUploadedFiles(array $uploadedFiles): ServerRequestInterface`
Returns immutably cloned object with updated uploaded files list.

**Parameters:**
- `$uploadedFiles` (array): UploadedFileInterface instances

#### `getParsedBody()`
Returns result of parsing request body. Automatically parses the following formats:
- JSON
- application/x-www-form-urlencoded
- multipart/form-data (non-file parts)

**Returns:** Parsed body (usually array)

#### `withParsedBody($data): ServerRequestInterface`
Returns immutably cloned object with updated parsed body.

**Parameters:**
- `$data` (mixed): New value to assign to parsed body (usually array)

#### `getAttributes(): array`
Returns custom attributes attached to request as an array. Attributes are used in middleware, router, framework-level logic.

#### `getAttribute(string $name, $default = null)`
Gets and returns value of one attribute by name.

**Parameters:**
- `$name` (string): Attribute name
- `$default` (mixed): Default value to return if not found

**Returns:** Attribute value or default

#### `withAttribute(string $name, $value): ServerRequestInterface`
Returns a new request instance (immutable) with one attribute added.

**Parameters:**
- `$name` (string): Attribute name
- `$value` (mixed): Value

#### `withoutAttribute(string $name): ServerRequestInterface`
Returns immutably cloned object with one attribute removed.

**Parameters:**
- `$name` (string): Name of attribute to remove

---

## 🔷 Uri

PSR-7 UriInterface implementation. Designed to manage URI component parts:
- Scheme (http, https)
- User info (user:password)
- Host (domain or IPv6)
- Port
- Path
- Query
- Fragment

### Methods

#### `getScheme(): string`
Returns URI scheme (http or https).

#### `setScheme(string $scheme): void`
Set scheme (mutable setter).

**Parameters:**
- `$scheme` (string): http or https

**Throws:** `\InvalidArgumentException` - If scheme is invalid

#### `getAuthority(): string`
Returns authority part (user@host:port) as a whole.

#### `getUserInfo(): string`
Returns user info (username or username:password).

#### `setUserInfo(string $user, ?string $password = null): void`
Set user info (mutable setter).

**Parameters:**
- `$user` (string): Username (encoded or unencoded)
- `$password` (string|null): Password (optional, encoded or unencoded)

#### `getHost(): string`
Returns host (example.com).

#### `setHost(string $host): void`
Set host (mutable setter). Converts IPv6 addresses to [xxxx:xxxx] format.

**Parameters:**
- `$host` (string): Host

#### `getPort(): ?int`
Returns port. Returns null for default ports (80, 443) (PSR-7 requirement).

#### `setPort(int $port): void`
Set port (mutable setter).

**Parameters:**
- `$port` (int): Valid range: 1–65535

**Throws:** `\InvalidArgumentException` - If port is invalid

#### `getPath(): string`
Returns URI path part.

#### `setPath(string $path): void`
Set path (mutable setter).

**Parameters:**
- `$path` (string): URI path part (encoded or unencoded)

#### `getQuery(): string`
Returns query string (part after ?, key=value&key2=value2).

#### `setQuery(string $query): void`
Set query (mutable setter).

**Parameters:**
- `$query` (string): Query string (key=value&key2=value2 format, encoded or unencoded)

#### `getFragment(): string`
Returns fragment (#info, etc.).

#### `setFragment(string $fragment): void`
Set fragment (mutable setter).

**Parameters:**
- `$fragment` (string): URI fragment (part after #, encoded or unencoded)

#### Immutable Methods

All `with*()` methods are immutable and return a new URI instance:

- `withScheme(string $scheme): UriInterface`
- `withUserInfo(string $user, ?string $password = null): UriInterface`
- `withHost(string $host): UriInterface`
- `withPort(?int $port): UriInterface`
- `withPath(string $path): UriInterface`
- `withQuery(string $query): UriInterface`
- `withFragment(string $fragment): UriInterface`

#### `__toString(): string`
Returns URI in full format (scheme://authority/path?query#fragment).

---

## 🔷 Stream

PSR-7 StreamInterface implementation - based on file resource. StreamInterface implementation based on PHP resource (returned by fopen()). Used for request body.

### Constructor

```php
public function __construct($resource)
```

**Parameters:**
- `$resource` (resource): PHP stream resource (returned by fopen())

**Throws:** `\InvalidArgumentException` - If $resource is not a resource

### Methods

#### `__toString(): string`
Returns all stream contents as string.

#### `close(): void`
Closes stream resource and closes stream.

#### `detach()`
Detaches stream resource and closes stream. Returns resource, but makes stream unusable.

**Returns:** resource|null - Stream resource or null (if detached)

#### `getSize(): ?int`
Returns stream size (in bytes).

**Returns:** Stream size or null (if detached)

#### `tell(): int`
Returns stream's current position.

**Throws:** `\RuntimeException` - If stream is detached or position cannot be determined

#### `eof(): bool`
Checks if stream has reached end (EOF - End Of File).

**Returns:** true if EOF, false if readable

#### `isSeekable(): bool`
Checks if stream is seekable.

**Returns:** true if seekable, false otherwise

#### `seek(int $offset, int $whence = SEEK_SET): void`
Changes stream position (seek).

**Parameters:**
- `$offset` (int): New position (in bytes)
- `$whence` (int): SEEK_SET (beginning), SEEK_CUR (current), SEEK_END (end)

**Throws:** `\RuntimeException` - If stream is not seekable or seek is not possible

#### `rewind(): void`
Rewinds stream position to beginning.

**Throws:** `\RuntimeException` - If stream is not seekable

#### `isWritable(): bool`
Checks if stream is writable.

**Returns:** true if writable, false otherwise

#### `write(string $string): int`
Writes data to stream.

**Parameters:**
- `$string` (string): String to write

**Returns:** Number of characters written

**Throws:** `\RuntimeException` - If stream is not writable or write is not possible

#### `isReadable(): bool`
Checks if stream is readable.

**Returns:** true if readable, false otherwise

#### `read(int $length): string`
Reads data from stream.

**Parameters:**
- `$length` (int): Number of characters to read

**Returns:** Read data

**Throws:** `\RuntimeException` - If stream is not readable or read is not possible

#### `getContents(): string`
Reads all remaining stream contents.

**Returns:** Remaining stream content

**Throws:** `\RuntimeException` - If stream is detached or not readable

#### `getMetadata(?string $key = null)`
Returns stream metadata.

**Parameters:**
- `$key` (string|null): Metadata key (null for all metadata)

**Returns:**
- array: All metadata (if key is null)
- mixed: Value of specific key
- null: Key not found or stream detached

---

## 🔷 UploadedFile

PSR-7 UploadedFileInterface implementation. Designed to manage uploaded file metadata and temporary file path (tmp_name).

### Constructor

```php
public function __construct(string $tmp_name, ?string $name, ?string $type, ?int $size, int $error)
```

**Parameters:**
- `$tmp_name` (string): Temporary file (tmp path)
- `$name` (string|null): Client filename
- `$type` (string|null): MIME type
- `$size` (int|null): File size
- `$error` (int): PHP upload error code

### Methods

#### `getClientFilename(): ?string`
Returns original client filename.

**Returns:** Original client filename

#### `getClientMediaType(): ?string`
Returns MIME type from client (e.g., image/jpeg).

**Returns:** MIME type

#### `getSize(): ?int`
Returns size of uploaded file (if available).

**Returns:** File size in bytes

#### `getError(): int`
Returns PHP upload error code.

**Returns:** One of PHP UPLOAD_ERR_* constants

#### `getStream(): StreamInterface`
Implementation to create stream from file. Throws exception as not supported.

**Throws:** `\RuntimeException`

#### `moveTo(string $targetPath): void`
Moves uploaded file from temporary folder to target location.

**Parameters:**
- `$targetPath` (string): Target file absolute or relative path

**Throws:**
- `\InvalidArgumentException` - If targetPath is empty
- `\RuntimeException` - If file doesn't exist or already moved
- `\RuntimeException` - If upload error occurred
- `\RuntimeException` - If error moving file or deleting temp file

#### `jsonSerialize(): mixed`
Values used when serializing UploadedFile object to JSON.

**Returns:** All object properties as key/value format

---

## 🔷 Output

Output stream – StreamInterface implementation based on PHP's output buffering. Special stream that works on the principle of "directly printing HTTP response body to browser".

### Constructor

```php
public function __construct()
```

Output buffering automatically starts when Output stream is created.

### Methods

#### `getBuffer(): OutputBuffer`
Returns OutputBuffer object.

#### `__toString(): string`
Returns all stream contents as string.

#### `close(): void`
Cleans and closes output buffering.

#### `detach()`
Cannot detach stream resource.

**Throws:** `\RuntimeException`

#### `getSize(): ?int`
Returns total stream size (buffer length).

#### `tell(): int`
Always returns 0 as stream is not seekable.

#### `eof(): bool`
Output stream is always in EOF (not readable) state.

**Returns:** true

#### `isSeekable(): bool`
Does not support seek.

**Returns:** false

#### `seek(int $offset, int $whence = SEEK_SET): void`
Throws error as seek is not possible.

**Throws:** `\RuntimeException`

#### `rewind(): void`
Throws error as rewind is not possible.

**Throws:** `\RuntimeException`

#### `isWritable(): bool`
This stream is write-only.

**Returns:** true

#### `write(string $string): int`
Immediately echoes written string to output.

**Parameters:**
- `$string` (string): String to write

**Returns:** Number of characters written

#### `isReadable(): bool`
Stream is not readable.

**Returns:** false

#### `read(int $length): string`
Throws error as read is not supported.

**Throws:** `\RuntimeException`

#### `getContents(): string`
Returns OutputBuffer's current content as string.

#### `getMetadata(?string $key = null)`
Output stream has no metadata concept, always returns null.

**Returns:** null

---

## 🔷 OutputBuffer

Wrapper class for PHP output buffering. Provides more organized, object-oriented way to use PHP's output buffer functions like ob_start(), ob_flush(), ob_get_contents().

### Methods

#### `start(int $chunk_size = 0, int $flags = PHP_OUTPUT_HANDLER_STDFLAGS): void`
Starts output buffering.

**Parameters:**
- `$chunk_size` (int): Buffer chunk size (0 = buffer)
- `$flags` (int): Output handler flags (PHP default: PHP_OUTPUT_HANDLER_STDFLAGS)

#### `startCallback(callable $output_callback, $chunk_size = 0, int $flags = PHP_OUTPUT_HANDLER_STDFLAGS): void`
Starts output buffering with callback.

**Parameters:**
- `$output_callback` (callable): Callback to process buffer
- `$chunk_size` (int): Chunk size
- `$flags` (int): Flags

#### `startCompress(): void`
Starts output buffering using compress() function. Converts HTML to whitespace-reduced, compressed format.

#### `flush(): void`
Outputs (flushes) current buffer contents and empties it.

#### `endClean(): void`
Removes buffer contents and closes buffer.

#### `endFlush(): void`
Flushes buffer and closes it.

#### `getLength(): int|false`
Returns buffer length.

**Returns:** Buffer size, or false

#### `getContents(): string|null|false`
Returns content inside buffer.

**Returns:**
- string: buffer content
- null: when buffer doesn't exist
- false: on error

#### `compress($buffer): string`
Compresses HTML output (whitespace compression).

**Parameters:**
- `$buffer` (string): HTML buffer

**Returns:** Compressed HTML

---

## 🔷 ReasonPhrase

Utility class containing standard reason phrases (value descriptions) for each HTTP status code. Centralized all common status text descriptions from PSR-7 and HTTP/1.1, HTTP/2 RFC standards as constants.

### Constants

Reason phrases for all HTTP status codes:

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

All classes comply with the following PSR-7 interfaces:

- `Psr\Http\Message\MessageInterface`
- `Psr\Http\Message\RequestInterface`
- `Psr\Http\Message\ResponseInterface`
- `Psr\Http\Message\UriInterface`
- `Psr\Http\Message\ServerRequestInterface`
- `Psr\Http\Message\StreamInterface`
- `Psr\Http\Message\UploadedFileInterface`
