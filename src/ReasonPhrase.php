<?php

namespace codesaur\Http\Message;

/**
 * HTTP статус код бүрийн стандарт reason phrase (утга тайлбар)-уудыг
 * агуулсан utility класс.
 *
 * PSR-7 болон HTTP/1.1, HTTP/2 RFC стандартуудад заасан нийтлэг
 * статусын текстэн тайлбаруудыг (reason phrases) тогтмол (constant)
 * хэлбэрээр нэг дор төвлөрүүлсэн.
 *
 * Энэ класс нь зөвхөн тогтмол утгууд агуулдаг ба ямар нэгэн
 * instance үүсгэх шаардлагагүй. Response объект үүсгэх эсвэл
 * статус кодыг хүн уншихад ойлгомжтой хэлбэрт хөрвүүлэх зэргээр
 * олон нийтлэг тохиолдолд ашиглагдана.
 *
 * Жишээ:
 *   $reason = ReasonPhrase::STATUS_404; // "Not Found"
 *
 * Ангиллууд:
 * - Informational (1xx)
 * - Successful (2xx)
 * - Redirection (3xx)
 * - Client Error (4xx)
 * - Server Error (5xx)
 */
class ReasonPhrase
{
    // -------------------------------------------------------------
    // Informational (1xx)
    // -------------------------------------------------------------

    /** @var string 100 Continue */
    const STATUS_100 = 'Continue';

    /** @var string 101 Switching Protocols */
    const STATUS_101 = 'Switching Protocols';

    /** @var string 102 Processing (WebDAV) */
    const STATUS_102 = 'Processing';

    /** @var string 103 Early Hints */
    const STATUS_103 = 'Early Hints';
    

    // -------------------------------------------------------------
    // Successful (2xx)
    // -------------------------------------------------------------

    /** @var string 200 OK */
    const STATUS_200 = 'OK';

    /** @var string 201 Created */
    const STATUS_201 = 'Created';

    /** @var string 202 Accepted */
    const STATUS_202 = 'Accepted';

    /** @var string 203 Non-Authoritative Information */
    const STATUS_203 = 'Non-Authoritative Information';

    /** @var string 204 No Content */
    const STATUS_204 = 'No Content';

    /** @var string 205 Reset Content */
    const STATUS_205 = 'Reset Content';

    /** @var string 206 Partial Content */
    const STATUS_206 = 'Partial Content';

    /** @var string 207 Multi-Status (WebDAV) */
    const STATUS_207 = 'Multi-Status';

    /** @var string 208 Already Reported (WebDAV) */
    const STATUS_208 = 'Already Reported';

    /** @var string 226 IM Used */
    const STATUS_226 = 'IM Used';


    // -------------------------------------------------------------
    // Redirection (3xx)
    // -------------------------------------------------------------

    /** @var string 300 Multiple Choices */
    const STATUS_300 = 'Multiple Choices';

    /** @var string 301 Moved Permanently */
    const STATUS_301 = 'Moved Permanently';

    /** @var string 302 Found */
    const STATUS_302 = 'Found';

    /** @var string 303 See Other */
    const STATUS_303 = 'See Other';

    /** @var string 304 Not Modified */
    const STATUS_304 = 'Not Modified';

    /** @var string 305 Use Proxy */
    const STATUS_305 = 'Use Proxy';

    /** @var string 306 Unused */
    const STATUS_306 = '(Unused)';

    /** @var string 307 Temporary Redirect */
    const STATUS_307 = 'Temporary Redirect';

    /** @var string 308 Permanent Redirect */
    const STATUS_308 = 'Permanent Redirect';


    // -------------------------------------------------------------
    // Client Errors (4xx)
    // -------------------------------------------------------------

    /** @var string 400 Bad Request */
    const STATUS_400 = 'Bad Request';

    /** @var string 401 Unauthorized */
    const STATUS_401 = 'Unauthorized';

    /** @var string 402 Payment Required */
    const STATUS_402 = 'Payment Required';

    /** @var string 403 Forbidden */
    const STATUS_403 = 'Forbidden';

    /** @var string 404 Not Found */
    const STATUS_404 = 'Not Found';

    /** @var string 405 Method Not Allowed */
    const STATUS_405 = 'Method Not Allowed';

    /** @var string 406 Not Acceptable */
    const STATUS_406 = 'Not Acceptable';

    /** @var string 407 Proxy Authentication Required */
    const STATUS_407 = 'Proxy Authentication Required';

    /** @var string 408 Request Timeout */
    const STATUS_408 = 'Request Timeout';

    /** @var string 409 Conflict */
    const STATUS_409 = 'Conflict';

    /** @var string 410 Gone */
    const STATUS_410 = 'Gone';

    /** @var string 411 Length Required */
    const STATUS_411 = 'Length Required';

    /** @var string 412 Precondition Failed */
    const STATUS_412 = 'Precondition Failed';

    /** @var string 413 Payload Too Large */
    const STATUS_413 = 'Payload Too Large';

    /** @var string 414 URI Too Long */
    const STATUS_414 = 'URI Too Long';

    /** @var string 415 Unsupported Media Type */
    const STATUS_415 = 'Unsupported Media Type';

    /** @var string 416 Range Not Satisfiable */
    const STATUS_416 = 'Range Not Satisfiable';

    /** @var string 417 Expectation Failed */
    const STATUS_417 = 'Expectation Failed';

    /** @var string 418 I'm a Teapot — RFC 2324 (April Fools) */
    const STATUS_418 = 'I\'m a Teapot';

    /** @var string 421 Misdirected Request */
    const STATUS_421 = 'Misdirected Request';

    /** @var string 422 Unprocessable Entity */
    const STATUS_422 = 'Unprocessable Entity';

    /** @var string 423 Locked */
    const STATUS_423 = 'Locked';

    /** @var string 424 Failed Dependency (WebDAV) */
    const STATUS_424 = 'Failed Dependency';

    /** @var string 425 Too Early */
    const STATUS_425 = 'Too Early';

    /** @var string 426 Upgrade Required */
    const STATUS_426 = 'Upgrade Required';

    /** @var string 428 Precondition Required */
    const STATUS_428 = 'Precondition Required';

    /** @var string 429 Too Many Requests */
    const STATUS_429 = 'Too Many Requests';

    /** @var string 431 Request Header Fields Too Large */
    const STATUS_431 = 'Request Header Fields Too Large';

    /** @var string 451 Unavailable For Legal Reasons */
    const STATUS_451 = 'Unavailable For Legal Reasons';


    // -------------------------------------------------------------
    // Server Errors (5xx)
    // -------------------------------------------------------------

    /** @var string 500 Internal Server Error */
    const STATUS_500 = 'Internal Server Error';

    /** @var string 501 Not Implemented */
    const STATUS_501 = 'Not Implemented';

    /** @var string 502 Bad Gateway */
    const STATUS_502 = 'Bad Gateway';

    /** @var string 503 Service Unavailable */
    const STATUS_503 = 'Service Unavailable';

    /** @var string 504 Gateway Timeout */
    const STATUS_504 = 'Gateway Timeout';

    /** @var string 505 HTTP Version Not Supported */
    const STATUS_505 = 'HTTP Version Not Supported';

    /** @var string 506 Variant Also Negotiates */
    const STATUS_506 = 'Variant Also Negotiates';

    /** @var string 507 Insufficient Storage */
    const STATUS_507 = 'Insufficient Storage';

    /** @var string 508 Loop Detected */
    const STATUS_508 = 'Loop Detected';

    /** @var string 510 Not Extended */
    const STATUS_510 = 'Not Extended';

    /** @var string 511 Network Authentication Required */
    const STATUS_511 = 'Network Authentication Required';
}

