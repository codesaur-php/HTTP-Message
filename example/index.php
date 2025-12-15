<?php

namespace codesaur\Http\Message\Example;

/**
 * codesaur HTTP-Message Component - Жишээ файл
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
use codesaur\Http\Message\Response;

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
$request->initFromGlobal();

// -------------------------------------------------------------
// 2. Request мэдээллийг харуулах
// -------------------------------------------------------------

?>
<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>codesaur HTTP-Message - Жишээ</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
            border-left: 4px solid #4CAF50;
            padding-left: 15px;
        }
        .info-box {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin: 15px 0;
        }
        .info-box strong {
            color: #4CAF50;
            display: inline-block;
            min-width: 150px;
        }
        .method {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
        }
        .uri {
            color: #2196F3;
            font-family: monospace;
            word-break: break-all;
        }
        .headers, .params {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin: 10px 0;
        }
        .headers table, .params table {
            width: 100%;
            border-collapse: collapse;
        }
        .headers th, .params th {
            background: #4CAF50;
            color: white;
            padding: 10px;
            text-align: left;
        }
        .headers td, .params td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
        }
        .headers tr:hover, .params tr:hover {
            background: #f5f5f5;
        }
        .empty {
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📨 codesaur HTTP-Message Component</h1>
        <p>PSR-7 стандартын дагуу HTTP хүсэлтийн мэдээлэл</p>

        <h2>1. Хүсэлтийн үндсэн мэдээлэл</h2>
        <div class="info-box">
            <strong>HTTP Method:</strong>
            <span class="method"><?= htmlspecialchars($request->getMethod() ?: 'GET') ?></span>
        </div>
        <div class="info-box">
            <strong>Request URI:</strong>
            <span class="uri"><?= htmlspecialchars((string) $request->getUri()) ?></span>
        </div>
        <?php
        $uri = $request->getUri();
        ?>
        <div class="info-box">
            <strong>URI Components:</strong>
            <div style="margin-top: 10px; font-family: monospace; font-size: 14px;">
                <div><strong>Scheme:</strong> <?= htmlspecialchars($uri->getScheme() ?: '(none)') ?></div>
                <div><strong>Host:</strong> <?= htmlspecialchars($uri->getHost() ?: '(none)') ?></div>
                <?php if ($uri->getPort() !== null): ?>
                <div><strong>Port:</strong> <?= htmlspecialchars($uri->getPort()) ?></div>
                <?php endif; ?>
                <div><strong>Path:</strong> <?= htmlspecialchars($uri->getPath() ?: '/') ?></div>
                <?php if ($uri->getQuery() !== ''): ?>
                <div><strong>Query:</strong> <?= htmlspecialchars($uri->getQuery()) ?></div>
                <?php endif; ?>
                <?php if ($uri->getFragment() !== ''): ?>
                <div><strong>Fragment:</strong> <span style="color: #2196F3;">#<?= htmlspecialchars($uri->getFragment()) ?></span></div>
                <?php endif; ?>
                <div id="fragment-display" style="display: none;">
                    <strong>Fragment (from browser):</strong> <span style="color: #2196F3;" id="fragment-value"></span>
                </div>
            </div>
        </div>
        <div class="info-box">
            <strong>Request Target:</strong>
            <span class="uri"><?= htmlspecialchars($request->getRequestTarget()) ?></span>
        </div>
        <div class="info-box">
            <strong>Protocol Version:</strong>
            <?= htmlspecialchars($request->getProtocolVersion()) ?>
        </div>

        <h2>2. Headers</h2>
        <div class="headers">
            <?php
            $headers = $request->getHeaders();
            if (empty($headers)) {
                echo '<p class="empty">Header байхгүй</p>';
            } else {
                echo '<table>';
                echo '<tr><th>Header Name</th><th>Value</th></tr>';
                foreach ($headers as $name => $values) {
                    echo '<tr>';
                    echo '<td><strong>' . htmlspecialchars($name) . '</strong></td>';
                    echo '<td>' . htmlspecialchars(implode(', ', $values)) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
            ?>
        </div>

        <h2>3. Query Parameters</h2>
        <div class="params">
            <?php
            $queryParams = $request->getQueryParams();
            if (empty($queryParams)) {
                echo '<p class="empty">Query parameter байхгүй</p>';
            } else {
                echo '<table>';
                echo '<tr><th>Parameter</th><th>Value</th></tr>';
                foreach ($queryParams as $key => $value) {
                    echo '<tr>';
                    echo '<td><strong>' . htmlspecialchars($key) . '</strong></td>';
                    echo '<td>' . htmlspecialchars(is_array($value) ? json_encode($value) : $value) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
            ?>
        </div>

        <h2>4. Cookies</h2>
        <div class="params">
            <?php
            $cookies = $request->getCookieParams();
            if (empty($cookies)) {
                echo '<p class="empty">Cookie байхгүй</p>';
            } else {
                echo '<table>';
                echo '<tr><th>Cookie Name</th><th>Value</th></tr>';
                foreach ($cookies as $name => $value) {
                    echo '<tr>';
                    echo '<td><strong>' . htmlspecialchars($name) . '</strong></td>';
                    echo '<td>' . htmlspecialchars($value) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
            ?>
        </div>

        <h2>5. Response жишээ</h2>
        <div class="info-box">
            <p>Response объект ашиглан JSON хариу буцаах жишээ:</p>
            <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;">
<?php
$response = new Response();
$response = $response->withStatus(200)
    ->withHeader('Content-Type', 'application/json; charset=utf-8');

$data = [
    'status' => 'success',
    'message' => 'codesaur HTTP-Message Component амжилттай ажиллаж байна!',
    'request' => [
        'method' => $request->getMethod(),
        'uri' => (string) $request->getUri(),
        'headers' => $request->getHeaders(),
    ]
];

// Анхаар: Response-ийн default body нь output buffer тул
// write() хийгдэх бүрт шууд browser/клиент рүү хэвлэгдэнэ
$response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
?>
            </pre>
        </div>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">
        <p style="text-align: center; color: #999; font-size: 14px;">
            codesaur HTTP-Message Component - PSR-7 Implementation<br>
            <a href="https://github.com/codesaur-php/HTTP-Message" style="color: #4CAF50;">GitHub</a>
        </p>
    </div>
    <script>
        // Fragment нь HTTP протоколын дагуу сервер рүү илгээгддэггүй тул
        // JavaScript ашиглан browser-ээс уншиж харуулна
        (function() {
            var hash = window.location.hash;
            if (hash && hash.length > 1) {
                var fragmentValue = hash.substring(1); // #-ийг арилгах
                var fragmentDisplay = document.getElementById('fragment-display');
                var fragmentValueEl = document.getElementById('fragment-value');
                if (fragmentDisplay && fragmentValueEl) {
                    fragmentValueEl.textContent = '#' + fragmentValue;
                    fragmentDisplay.style.display = 'block';
                }
            }
        })();
    </script>
</body>
</html>
