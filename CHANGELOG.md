# Changelog

This file contains all changes for all versions of the `codesaur/http-message` package.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [3.0.0] - 2026-01-08
[3.0.0]: https://github.com/codesaur-php/HTTP-Message/compare/v2.0.0...v3.0.0

### ✨ Added

- **Documentation Improvements**
  - Comprehensive bilingual documentation structure (Mongolian & English)
  - Documentation reorganized into `docs/` directory
    - `docs/mn/README.md` - Монгол хэл дээрх бүрэн танилцуулга
    - `docs/mn/api.md` - Монгол хэл дээрх API тайлбар
    - `docs/mn/review.md` - Монгол хэл дээрх код шалгалтын тайлан
    - `docs/en/README.md` - Full documentation in English
    - `docs/en/api.md` - Complete API reference in English
    - `docs/en/review.md` - Code review report in English
  - Improved README.md with bilingual support and table of contents
  - Better structured documentation with cross-references

- **Project Infrastructure**
  - GitHub Actions CI/CD workflow (`.github/workflows/ci.yml`)
    - Automated testing on PHP 8.2, 8.3, 8.4
    - Multi-platform support (Ubuntu & Windows)
    - Automated test runs on push and pull requests
    - Code coverage integration with Codecov
  - Contributing guidelines (`.github/CONTRIBUTING.md`)
  - Security policy (`.github/SECURITY.md`)

- **Composer Scripts**
  - Enhanced composer.json scripts for better developer experience
  - Improved test coverage reporting configuration

### 🔧 Improved

- **Documentation Quality**
  - Enhanced README with better structure and examples
  - Improved API documentation formatting
  - Better code examples and usage patterns
  - Clearer bilingual content organization

- **Project Structure**
  - Better organized documentation files
  - Improved project maintainability
  - Enhanced developer onboarding experience

### 📝 Changed

- Documentation files moved from root to `docs/` directory
- README structure improved for better readability
- Enhanced composer.json description (minor text improvements)

---

## [2.0.0] - 2025-12-17
[2.0.0]: https://github.com/codesaur-php/HTTP-Message/compare/v1.0...v2.0.0

### ✨ Added

- **New Classes**
  - `Stream` - Full PSR-7 StreamInterface implementation based on PHP resources
    - Supports readable, writable, and seekable streams
    - Works with `php://temp`, `php://memory`, file streams, and all PHP stream types
    - Used automatically for request body in `Message::getBody()`
    - Includes methods: `tell()`, `seek()`, `rewind()`, `eof()`, `getContents()`, etc.
  - `UploadedFile` - Complete PSR-7 UploadedFileInterface implementation
    - Handles PHP upload files and converts them to PSR-7 format
    - Includes `moveTo()` method for safe file upload handling
    - Supports all uploaded file metadata (client filename, size, MIME type, error code)
  - `NonBodyResponse` - Response class without body stream
    - Designed for responses that work directly with output buffer
    - Allows direct printing to browser using `echo`, `print` without body stream
    - Ideal for responses like 301, 204, 304 that don't require body content
  - `ReasonPhrase` - Utility class with HTTP status code reason phrases
    - Contains all standard HTTP status code text descriptions (1xx, 2xx, 3xx, 4xx, 5xx)
    - Replaces the old `ReasonPrhaseInterface` (typo fixed)
    - Organized by status code categories with PHPDoc comments

- **Enhanced Features**
  - **Advanced Multipart Parser**
    - RFC 7578 compliant multipart/form-data parser
    - Supports multi-level array uploads (e.g., `files[0][name]`)
    - Handles multiple file inputs with same name
    - Properly handles empty filename cases ("No file selected")
    - JSON, Raw body, and urlencoded body fallback support
    - Automatic conversion to `UploadedFile` instances
  - **Improved ServerRequest**
    - Enhanced `initFromGlobal()` method
    - Better handling of complex form data structures
    - Improved parsing of uploaded files

- **Testing Infrastructure**
  - Complete PHPUnit test suite
    - `MessageTest.php` - Tests for base Message class
    - `RequestTest.php` - Request interface tests
    - `ResponseTest.php` - Response interface tests
    - `NonBodyResponseTest.php` - NonBodyResponse specific tests
    - `UriTest.php` - URI handling tests
    - `UploadedFileTest.php` - File upload tests
    - `OutputTest.php` - Output buffer tests
    - `OutputBufferTest.php` - Buffer management tests
    - `EdgeCaseTest.php` - Edge cases and boundary conditions
    - `Integration/FullRequestResponseTest.php` - Full integration tests
  - PHPUnit configuration file (`phpunit.xml`)
  - Test coverage reporting (HTML, text, XML formats)

- **Documentation**
  - Comprehensive API documentation (`API.md`)
    - Complete PHPDoc comments for all classes, methods, and properties
    - Usage examples and parameter descriptions
    - Exception documentation
  - Code review report (`REVIEW.md`)
    - Package architecture analysis
    - Code quality assessment
    - Usage possibilities and best practices
  - Enhanced README.md with detailed examples
  - Complete PHPDoc for all classes, methods, and properties

### 🔧 Improved

- **PSR-7 Compliance**
  - Upgraded from PSR-7 v1.0.1 to PSR-7 v2.0
  - Full compliance with all PSR-7 interfaces
  - Improved immutable pattern implementation
  - Better error handling and validation

- **Code Quality**
  - Complete PHPDoc documentation for all classes
  - Better code organization and structure
  - Improved exception handling
  - Enhanced validation logic
  - Code refactoring for better maintainability

- **Performance**
  - Optimized multipart parser
  - Improved stream handling efficiency
  - Better memory management

### 📝 Changed

- **PHP Version Requirement**
  - Upgraded from PHP >=7.2.0 to PHP >=8.2.1
  - Leverages PHP 8.2+ features (readonly properties, improved types, etc.)

- **Dependencies**
  - PSR-7 upgraded from `>=1.0.1` to `>=2.0`
  - PHPUnit added as dev dependency (`^10.0`)
  - Updated `fig/http-message-util` requirement

- **Class Renaming/Fixing**
  - `ReasonPrhaseInterface` → `ReasonPhrase` (fixed typo, changed from interface to class)

- **Removed**
  - `RequestMethods` class removed (functionality integrated elsewhere)

- **Composer Configuration**
  - Added `autoload-dev` section for test namespace
  - Added `require-dev` section with PHPUnit
  - Added composer scripts for testing and coverage

---

## [1.0] - 2021-03-02
[1.0]: https://github.com/codesaur-php/HTTP-Message/releases/tag/v1.0

### ✨ Added

- **Core PSR-7 Implementation**
  - `Message` (abstract) - Base implementation of PSR-7 MessageInterface
    - HTTP protocol version management
    - Header management (case-insensitive)
    - StreamInterface body management
    - Immutable pattern implementation
  - `Request` - PSR-7 RequestInterface implementation
    - HTTP method support
    - Request URI handling
    - Request target management
  - `Response` - PSR-7 ResponseInterface implementation
    - HTTP status code handling
    - Reason phrase support
    - Response body management via Output stream
  - `ServerRequest` - PSR-7 ServerRequestInterface implementation
    - Server parameters support
    - Cookie handling
    - Query parameters
    - Parsed body support
    - Uploaded files handling (basic)

- **Utility Classes**
  - `Uri` - PSR-7 UriInterface implementation
    - Scheme, host, port, path, query, fragment support
    - URI validation and normalization
  - `Output` - StreamInterface implementation using output buffering
    - Direct output to browser/client
    - Stream-like interface for response body
  - `OutputBuffer` - Buffer management utility
    - Minify functionality
    - Compress functionality
    - Flush and clean operations
  - `ReasonPrhaseInterface` - Interface with HTTP status code constants
    - All standard HTTP status codes (1xx, 2xx, 3xx, 4xx, 5xx)
    - Note: Contains typo in name (fixed in v2.0.0)
  - `RequestMethods` - Request method constants utility
    - Implements Fig\Http\Message\RequestMethodInterface

- **Basic Documentation**
  - Initial README.md
  - Basic usage examples
  - License file (MIT)

### 📋 Specifications

- **PHP Version:** >=7.2.0
- **PSR-7:** >=1.0.1
- **Dependencies:** 
  - `psr/http-message`: >=1.0.1
  - `fig/http-message-util`: >=1.1.5
  - `ext-json`: *

### 🎯 Initial Features

- Basic PSR-7 compliance
- Core HTTP message components
- Simple request/response handling
- Basic URI manipulation
- Output buffering support
- Framework-agnostic design

---

**Legend:**
- ✨ Added - New features
- 🔧 Improved - Enhancements to existing features
- 📝 Changed - Changes in existing functionality
- 🗑️ Removed - Removed features
- 🔒 Security - Security-related changes
