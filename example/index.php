<?php

namespace codesaur\Http\Message\Example;

/**
 * Codesaur HTTP-Message Component — Жишээ файл
 *
 * Энэ жишээ нь ServerRequest класс ашиглан 
 * HTTP хүсэлтийн глобал орчны мэдээллийг уншиж,
 * PSR-7 дагуу Request объект үүсгэхийг харуулна.
 *
 * DEV: v2.2025.11.30
 */

\ini_set('display_errors', 'On');
\error_reporting(\E_ALL);

require_once '../vendor/autoload.php';

use codesaur\Http\Message\ServerRequest;

// -------------------------------------------------------------
// 1. ServerRequest объект үүсгэх
// -------------------------------------------------------------

/**
 * ServerRequest нь PSR-7 стандартын дагуу HTTP хүсэлтийг илэрхийлнэ.
 * initFromGlobal() нь:
 *   - $_SERVER
 *   - $_COOKIE
 *   - $_FILES
 *   - $_POST, php://input
 *   - REQUEST_URI, QUERY_STRING
 * эдгээрийг автоматаар уншиж тохируулдаг.
 */
$request = new ServerRequest();

// Глобал орчноос Request бүхлээр нь initialize хийх
$request->initFromGlobal();

// -------------------------------------------------------------
// 2. Демо зорилгоор бүтэн request объект хэвлэх
// -------------------------------------------------------------

/**
 * var_dump() — зөвхөн жишээ харах зорилготой.
 * Жинхэнэ production орчинд request properties-ийг
 * лог бичих, middleware-д дамжуулах зэргээр шаардлагай бүх нөхцөлд ашиглана.
 */
var_dump($request);
