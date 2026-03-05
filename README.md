# codesaur/http-message

[![CI](https://github.com/codesaur-php/HTTP-Message/actions/workflows/ci.yml/badge.svg)](https://github.com/codesaur-php/HTTP-Message/actions/workflows/ci.yml)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2.1-777BB4.svg?logo=php)](https://www.php.net/)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## Агуулга / Table of Contents

1. [Монгол](#1-монгол-тайлбар) | 2. [English](#2-english-description) | 3. [Getting Started](#3-getting-started)

---

## 1. Монгол тайлбар

Цэвэр, минимал, объект хандалтат бүтэцтэй HTTP Message компонент (PSR-7).

`codesaur/http-message` нь **codesaur ecosystem**-ийн нэг хэсэг бөгөөд PHP-ийн PSR-7 стандартын дагуу **Request**, **Response**, **ServerRequest**, **URI**, **Stream**, **UploadedFile**, **OutputBuffer** зэрэг HTTP мессежийн бүрэлдэхүүнүүдийг хэрэгжүүлсэн бага жинтэй компонент юм.

Багц нь дараах үндсэн классуудаас бүрдэнэ:

- **Message** - PSR-7 MessageInterface хэрэгжилт (headers, protocol, body)
- **Request** - PSR-7 RequestInterface
- **Response** - PSR-7 ResponseInterface
- **ServerRequest** - Глобал орчноос request сэргээдэг advanced implementation
- **Uri** - PSR-7 UriInterface
- **Stream** - PSR-7 StreamInterface хэрэгжилт
- **UploadedFile** - Upload хийгдсэн файлын metadata + moveTo()
- **Output** - StreamInterface хэрэгжилт (output buffering)

### Дэлгэрэнгүй мэдээлэл

- [Бүрэн танилцуулга](docs/mn/README.md) - Суурилуулалт, хэрэглээ, жишээнүүд
- [API тайлбар](docs/mn/api.md) - Бүх метод, exception-үүдийн тайлбар
- [Шалгалтын тайлан](docs/mn/review.md) - Код шалгалтын тайлан

---

## 2. English description

Clean, minimal, object-oriented HTTP Message component (PSR-7). A lightweight component that implements HTTP message components following PHP's PSR-7 standard, including **Request**, **Response**, **ServerRequest**, **URI**, **Stream**, **UploadedFile**, **OutputBuffer**, and more.

`codesaur/http-message` is part of the **codesaur ecosystem** and is a lightweight PHP component that can be used standalone, independent of any framework.

The package consists of the following core classes:

- **Message** - PSR-7 MessageInterface implementation (headers, protocol, body)
- **Request** - PSR-7 RequestInterface
- **Response** - PSR-7 ResponseInterface
- **ServerRequest** - Advanced implementation that reconstructs request from global environment
- **Uri** - PSR-7 UriInterface
- **Stream** - PSR-7 StreamInterface implementation
- **UploadedFile** - Uploaded file metadata + moveTo()
- **Output** - StreamInterface implementation (output buffering)

### Documentation

- [Full Documentation](docs/en/README.md) - Installation, usage, examples
- [API Reference](docs/en/api.md) - Complete API documentation
- [Review](docs/en/review.md) - Code review report

---

## 3. Getting Started

### Requirements

- PHP **8.2.1+**
- **ext-json** PHP extension
- Composer

### Installation

Composer ашиглан суулгана / Install via Composer:

```bash
composer require codesaur/http-message
```

### Quick Example

```php
use codesaur\Http\Message\ServerRequest;
use codesaur\Http\Message\Response;

// ServerRequest үүсгэх / Create ServerRequest
$request = (new ServerRequest())->initFromGlobal();

// Query params
var_dump($request->getQueryParams());

// Response үүсгэх / Create Response
$response = (new Response())
    ->withStatus(200)
    ->withHeader('Content-Type', 'application/json');

// Body-д бичих / Write to body
$response->getBody()->write(json_encode(['message' => 'Hello, World!']));
```

### Running Tests

Тест ажиллуулах / Run tests:

```bash
# Бүх тестүүдийг ажиллуулах / Run all tests
composer test

# Coverage-тэй тест ажиллуулах / Run tests with coverage
composer test-coverage
```

---

## Changelog

- [CHANGELOG.md](CHANGELOG.md) - Full version history

## Contributing & Security

- [Contributing Guide](.github/CONTRIBUTING.md)
- [Security Policy](.github/SECURITY.md)

## License

This project is licensed under the MIT License.

## Author

**Narankhuu**
codesaur@gmail.com
https://github.com/codesaur

**codesaur ecosystem:** https://codesaur.net
