# 📨 codesaur/http-message  
**PHP 8.2+ зориулсан минимал, цэвэр бүтэцтэй HTTP Message компонент (PSR-7)** 

`codesaur/http-message` нь PHP-ийн PSR-7 стандартын дагуу **Request**, **Response**,  
**ServerRequest**, **URI**, **Stream**, **UploadedFile**, **OutputBuffer** зэрэг HTTP  
мессежийн бүрэлдэхүүнүүдийг цэвэр, объект хандалтат хэлбэрээр хэрэгжүүлсэн бага жинтэй,  
minimal загвар бүхий компонент юм.

---

## 📌 Онцлог

- ✔ **PSR-7 MessageInterface, RequestInterface, ResponseInterface** бүрэн хэрэгжилт  
- ✔ `ServerRequest::initFromGlobal()` — глобал орчноос request үүсгэх advanced parser  
- ✔ `multipart/form-data` **бүрэн multipart parser** (RFC 7578 дагуу)  
- ✔ `UploadedFile` — PHP upload файлыг PSR-7 хэлбэрт хөрвүүлнэ  
- ✔ `Output` — response body-г output buffering-аар удирдах stream  
- ✔ `Uri` — scheme, host, path, query, fragment зэрэг URI бүрэлдэхүүн  
- ✔ Сервер болон CLI орчинд адил ажиллана  
- ✔ 0 external dependency (зөвхөн PSR interface-ууд)  
- ✔ Framework-agnostic тул codesaur, Laravel, Symfony, Slim болон бусад бүх PHP framework-тэй бүрэн нийцтэй  

---

## 📦 Суурилуулалт

```bash
composer require codesaur/http-message
```

---

## 📁 Бүтэц

| Файл | Үүрэг |
|------|-------|
| `Message` | PSR-7 MessageInterface хэрэгжилт (headers, protocol, body) |
| `Request` | PSR-7 RequestInterface |
| `Response` | PSR-7 ResponseInterface |
| `NonBodyResponse` | Body шаардлагагүй response (301, 204, 304 гэх мэт) |
| `ServerRequest` | Глобал орчноос request сэргээдэг advanced implementation |
| `Uri` | PSR-7 UriInterface |
| `UploadedFile` | Upload хийгдсэн файлын metadata + moveTo() |
| `Output` | StreamInterface хэрэгжилт (output buffering) |
| `OutputBuffer` | Minify, compress, flush, endClean зэрэг буфер удирдлага |
| `ReasonPhrase` | Статус кодын текстэн тайлбарууд |

---

# 🧩 Ашиглах жишээ

## 1. ServerRequest үүсгэх (глобал $_SERVER, $_POST, $_FILES, …)

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

## 2. Response ашиглан текст бичих

```php
use codesaur\Http\Message\Response;

$response = new Response();
$response = $response->withStatus(200);

$body = $response->getBody();
$body->write("<h1>Hello from Codesaur!</h1>");

echo $response->getBody();
```

---

## 3. JSON response буцаах жишээ

```php
use codesaur\Http\Message\Response;

$data = ['status' => 'success', 'message' => 'Hello world'];

$response = (new Response())
    ->withHeader('Content-Type', 'application/json');

$response->getBody()->write(json_encode($data));

echo $response->getBody();
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

# ⚙ Дотоод ажиллагааны онцлох хэсгүүд

## ✔ **Multipart/form-data Parser**

`ServerRequest::parseFormData()` нь RFC 7578-д нийцсэн хүчирхэг multipart parser бөгөөд:

- Олон түвшинтэй массив upload  
- Нэг нэртэй олон file input  
- Хоосон filename (“No file selected”)  
- JSON + Raw body + urlencoded body fallback  
- `UploadedFile` instance руу автоматаар хөрвүүлэлт  

зэрэг бүгдийг дэмжинэ.

---

## ✔ **Output Buffer — StreamInterface хэрэгжилт**

`Output` болон `OutputBuffer` нь response body-г дараах байдлаар удирддаг:

- output buffering эхлүүлэх  
- flush / clean / endFlush  
- автомат whitespace-minify (`compress()`)  
- String-cast → body контентыг буцаана  

---

## ↔ PSR-7 нийцтэй байдал

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

# 📄 Лиценз

Энэ төсөл MIT лицензтэй.

---

# 👨‍💻 Хөгжүүлэгч

Narankhuu  
📧 codesaur@gmail.com  
📱 +976 99000287  
🌐 https://github.com/codesaur  

---

# 🤝 Хөгжүүлэлтэд хувь нэмэр оруулах

Pull request буюу code засвар, сайжруулалтыг хэзээд нээлттэй хүлээж авна.  
Bug report илгээхдээ системийн орчны мэдээллээ давхар бичиж өгнө үү.
