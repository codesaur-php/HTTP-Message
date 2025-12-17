<?php

namespace codesaur\Http\Message\Tests;

use PHPUnit\Framework\TestCase;

use codesaur\Http\Message\Request;
use codesaur\Http\Message\Response;
use codesaur\Http\Message\Uri;
use codesaur\Http\Message\Stream;
use codesaur\Http\Message\UploadedFile;
use codesaur\Http\Message\ServerRequest;

/**
 * Edge case тестүүд - хязгаарын тохиолдлууд, буруу утга, тусгай кейсүүд
 */
class EdgeCaseTest extends TestCase
{
    private int $initialBufferLevel = 0;

    protected function setUp(): void
    {
        $this->initialBufferLevel = ob_get_level();
    }

    protected function tearDown(): void
    {
        while (ob_get_level() > $this->initialBufferLevel) {
            ob_end_clean();
        }
    }

    // ========== Message Edge Cases ==========

    public function testProtocolVersionEdgeCases(): void
    {
        $request = new Request();
        
        // Бүх дэмжигдэх хувилбарууд
        $versions = ['1', '1.0', '1.1', '2', '2.0', '3', '3.0'];
        foreach ($versions as $version) {
            $newRequest = $request->withProtocolVersion($version);
            $this->assertEquals($version, $newRequest->getProtocolVersion());
        }
    }

    public function testHeaderCaseInsensitive(): void
    {
        $request = new Request();
        $request = $request->withHeader('Content-Type', 'application/json');
        
        // Бүх case-үүд ажиллах ёстой
        $this->assertTrue($request->hasHeader('Content-Type'));
        $this->assertTrue($request->hasHeader('content-type'));
        $this->assertTrue($request->hasHeader('CONTENT-TYPE'));
        $this->assertTrue($request->hasHeader('CoNtEnT-TyPe'));
    }

    public function testHeaderMultipleValues(): void
    {
        $request = new Request();
        $request = $request->withHeader('Accept', 'application/json');
        $request = $request->withAddedHeader('Accept', 'text/html');
        $request = $request->withAddedHeader('Accept', 'text/plain');
        
        $values = $request->getHeader('Accept');
        $this->assertCount(3, $values);
        $this->assertEquals('application/json,text/html,text/plain', $request->getHeaderLine('Accept'));
    }

    public function testHeaderEmptyValue(): void
    {
        $request = new Request();
        $request = $request->withHeader('X-Custom', '');
        
        $this->assertTrue($request->hasHeader('X-Custom'));
        $this->assertEquals([''], $request->getHeader('X-Custom'));
    }

    public function testBodyLazyInitialization(): void
    {
        $request = new Request();
        
        // Body-г хэдэн удаа дуудахад ижил instance буцаана
        $body1 = $request->getBody();
        $body2 = $request->getBody();
        $this->assertSame($body1, $body2);
    }

    // ========== Request Edge Cases ==========

    public function testRequestTargetEmptyUri(): void
    {
        $request = new Request();
        // URI байхгүй үед "/" буцаана
        $this->assertEquals('/', $request->getRequestTarget());
    }

    public function testRequestTargetSpecialCharacters(): void
    {
        $request = new Request();
        $request = $request->withRequestTarget('/api/users?name=John%20Doe&age=30#section');
        
        $this->assertEquals('/api/users?name=John%20Doe&age=30#section', $request->getRequestTarget());
    }

    public function testRequestTargetAsterisk(): void
    {
        $request = new Request();
        $request = $request->withRequestTarget('*');
        
        $this->assertEquals('*', $request->getRequestTarget());
    }

    public function testMethodCaseNormalization(): void
    {
        $request = new Request();
        
        // Lowercase method-ийг uppercase болгоно
        $request = $request->withMethod('get');
        $this->assertEquals('GET', $request->getMethod());
        
        $request = $request->withMethod('post');
        $this->assertEquals('POST', $request->getMethod());
    }

    public function testUriPreserveHostLogic(): void
    {
        $request = new Request();
        $uri = new Uri();
        $uri->setHost('example.com');
        
        // preserveHost = true, Host header байхгүй → Host header нэмнэ
        $request = $request->withUri($uri, true);
        $this->assertEquals('example.com', $request->getHeaderLine('Host'));
        
        // preserveHost = true, Host header байна → хадгална
        $request = $request->withHeader('Host', 'original.com');
        $request = $request->withUri($uri, true);
        $this->assertEquals('original.com', $request->getHeaderLine('Host'));
        
        // preserveHost = false → Host header солино
        $request = $request->withUri($uri, false);
        $this->assertEquals('example.com', $request->getHeaderLine('Host'));
    }

    // ========== Response Edge Cases ==========

    public function testResponseStatusEdgeCases(): void
    {
        $response = new Response();
        
        // 1xx - Informational
        $response = $response->withStatus(100);
        $this->assertEquals(100, $response->getStatusCode());
        $this->assertEquals('Continue', $response->getReasonPhrase());
        
        // 2xx - Success
        $response = $response->withStatus(201);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Created', $response->getReasonPhrase());
        
        // 3xx - Redirection
        $response = $response->withStatus(301);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('Moved Permanently', $response->getReasonPhrase());
        
        // 4xx - Client Error
        $response = $response->withStatus(404);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Not Found', $response->getReasonPhrase());
        
        // 5xx - Server Error
        $response = $response->withStatus(500);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Internal Server Error', $response->getReasonPhrase());
    }

    public function testResponseCustomReasonPhrase(): void
    {
        $response = new Response();
        $response = $response->withStatus(200, 'Everything is OK');
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Everything is OK', $response->getReasonPhrase());
    }

    public function testResponseEmptyReasonPhrase(): void
    {
        $response = new Response();
        $response = $response->withStatus(200, '');
        
        // Хоосон reason phrase → стандарт утга ашиглана
        $this->assertEquals('OK', $response->getReasonPhrase());
    }

    // ========== Uri Edge Cases ==========

    public function testUriPortDefaultValues(): void
    {
        $uri = new Uri();
        
        // HTTP default port 80 → null буцаана
        $uri->setScheme('http');
        $uri->setPort(80);
        $this->assertNull($uri->getPort());
        
        // HTTPS default port 443 → null буцаана
        $uri->setScheme('https');
        $uri->setPort(443);
        $this->assertNull($uri->getPort());
        
        // HTTP port 8080 → null буцаана (special case)
        $uri->setScheme('http');
        $uri->setPort(8080);
        $this->assertNull($uri->getPort());
        
        // Бусад порт → port буцаана
        $uri->setPort(8081);
        $this->assertEquals(8081, $uri->getPort());
    }

    public function testUriIPv6Host(): void
    {
        $uri = new Uri();
        $uri->setHost('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
        
        // IPv6 хаяг [ ] дотор байх ёстой
        $this->assertEquals('[2001:0db8:85a3:0000:0000:8a2e:0370:7334]', $uri->getHost());
    }

    public function testUriUserInfoEncoding(): void
    {
        $uri = new Uri();
        $uri->setUserInfo('user@domain', 'pass:word');
        
        // Authority-д encode хийгдэнэ
        $authority = $uri->getAuthority();
        $this->assertStringContainsString('user%40domain', $authority);
        $this->assertStringContainsString('pass%3Aword', $authority);
    }

    public function testUriEmptyComponents(): void
    {
        $uri = new Uri();
        
        // Бүх компонент хоосон
        $this->assertEquals('', (string) $uri);
        
        // Зөвхөн scheme
        $uri->setScheme('https');
        $this->assertEquals('https:', (string) $uri);
        
        // Scheme + Host
        $uri->setHost('example.com');
        $this->assertEquals('https://example.com', (string) $uri);
    }

    public function testUriPathNormalization(): void
    {
        $uri = new Uri();
        
        // Path нь хадгалсан утгаар нь шууд ашиглагдана
        $uri->setPath('/api//users///');
        $this->assertEquals('/api//users///', $uri->getPath());
    }

    // ========== Stream Edge Cases ==========

    public function testStreamDetached(): void
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource);
        
        $this->assertFalse($stream->eof());
        
        $detached = $stream->detach();
        $this->assertIsResource($detached);
        
        // Detached болсны дараа exception
        $this->expectException(\RuntimeException::class);
        $stream->tell();
    }

    public function testStreamSeekable(): void
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource);
        
        $this->assertTrue($stream->isSeekable());
        
        $stream->write('Hello World');
        $stream->rewind();
        
        $this->assertEquals(0, $stream->tell());
        $this->assertEquals('Hello', $stream->read(5));
    }

    public function testStreamNonSeekable(): void
    {
        // php://input нь seekable биш, гэхдээ CLI-д ашиглах боломжгүй
        // Энэ тест нь зөвхөн documentation зориулалттай
        $this->markTestSkipped('php://input is not available in CLI mode for testing');
    }

    public function testStreamEmptyRead(): void
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource);
        
        // Хоосон stream-аас унших
        $this->assertEquals('', $stream->read(10));
        $this->assertTrue($stream->eof());
    }

    public function testStreamLargeWrite(): void
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource);
        
        // Том мэдээлэл бичих
        $largeData = str_repeat('A', 10000);
        $written = $stream->write($largeData);
        
        $this->assertEquals(10000, $written);
        $this->assertEquals(10000, $stream->getSize());
    }

    // ========== UploadedFile Edge Cases ==========

    public function testUploadedFileErrorCodes(): void
    {
        $tmpFile = sys_get_temp_dir() . '/test_' . uniqid() . '.txt';
        file_put_contents($tmpFile, 'test');
        
        // UPLOAD_ERR_NO_FILE - tmp_name хоосон байвал эхлээд InvalidArgumentException
        $file = new UploadedFile('', null, null, null, UPLOAD_ERR_NO_FILE);
        $this->expectException(\InvalidArgumentException::class);
        $file->moveTo(sys_get_temp_dir() . '/target.txt');
        
        // UPLOAD_ERR_INI_SIZE
        $file = new UploadedFile($tmpFile, 'test.txt', 'text/plain', 12, UPLOAD_ERR_INI_SIZE);
        $this->expectException(\RuntimeException::class);
        $file->moveTo(sys_get_temp_dir() . '/target.txt');
        
        // UPLOAD_ERR_FORM_SIZE
        $file = new UploadedFile($tmpFile, 'test.txt', 'text/plain', 12, UPLOAD_ERR_FORM_SIZE);
        $this->expectException(\RuntimeException::class);
        $file->moveTo(sys_get_temp_dir() . '/target.txt');
        
        unlink($tmpFile);
    }

    public function testUploadedFileNullValues(): void
    {
        $tmpFile = sys_get_temp_dir() . '/test_' . uniqid() . '.txt';
        file_put_contents($tmpFile, 'test');
        
        // Null утгуудтай файл
        $file = new UploadedFile($tmpFile, null, null, null, UPLOAD_ERR_OK);
        
        $this->assertNull($file->getClientFilename());
        $this->assertNull($file->getClientMediaType());
        $this->assertNull($file->getSize());
        $this->assertEquals(UPLOAD_ERR_OK, $file->getError());
        
        unlink($tmpFile);
    }

    public function testUploadedFileEmptyTmpName(): void
    {
        $file = new UploadedFile('', null, null, null, UPLOAD_ERR_NO_FILE);
        
        $this->expectException(\InvalidArgumentException::class);
        $file->moveTo(sys_get_temp_dir() . '/target.txt');
    }

    // ========== ServerRequest Edge Cases ==========

    public function testServerRequestEmptyGlobals(): void
    {
        // Глобал хувьсагч хоосон байх үед
        $originalServer = $_SERVER;
        $originalGet = $_GET;
        $originalPost = $_POST;
        $originalFiles = $_FILES;
        $originalCookie = $_COOKIE;
        
        // Minimal $_SERVER утгууд (initFromGlobal() шаардлагатай)
        // HTTP_HOST нь заавал байх ёстой, эсвэл хоосон string байх ёстой
        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'SERVER_PORT' => 80,
            'HTTPS' => 'off',
            'HTTP_HOST' => 'localhost' // HTTP_HOST заавал байх ёстой
        ];
        $_GET = [];
        $_POST = [];
        $_FILES = [];
        $_COOKIE = [];
        
        $request = new ServerRequest();
        try {
            $request->initFromGlobal();
            // Exception гарахгүй байх ёстой
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('Should not throw exception with minimal globals: ' . $e->getMessage());
        }
        
        // Restore
        $_SERVER = $originalServer;
        $_GET = $originalGet;
        $_POST = $originalPost;
        $_FILES = $originalFiles;
        $_COOKIE = $originalCookie;
    }

    public function testServerRequestQueryParamsLazy(): void
    {
        $request = new ServerRequest();
        $uri = new Uri();
        $uri->setQuery('key1=value1&key2=value2');
        $request = $request->withUri($uri);
        
        // Lazy evaluation - анхны дуудалт
        $params = $request->getQueryParams();
        $this->assertEquals('value1', $params['key1']);
        $this->assertEquals('value2', $params['key2']);
        
        // Дараагийн дуудалт - кешлэгдсэн утга
        $params2 = $request->getQueryParams();
        $this->assertSame($params, $params2);
    }

    public function testServerRequestAttributesNested(): void
    {
        $request = new ServerRequest();
        
        // Олон түвшинтэй attribute
        $request = $request->withAttribute('user', [
            'id' => 1,
            'name' => 'John',
            'roles' => ['admin', 'user']
        ]);
        
        $user = $request->getAttribute('user');
        $this->assertEquals(1, $user['id']);
        $this->assertEquals('John', $user['name']);
        $this->assertEquals(['admin', 'user'], $user['roles']);
        
        // Default утга
        $this->assertNull($request->getAttribute('nonexistent'));
        $this->assertEquals('default', $request->getAttribute('nonexistent', 'default'));
    }

    // ========== Immutability Edge Cases ==========

    public function testImmutabilityChain(): void
    {
        $request = new Request();
        
        // Олон удаа chain хийх
        $request = $request
            ->withMethod('POST')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withProtocolVersion('2.0');
        
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
        $this->assertEquals('application/json', $request->getHeaderLine('Accept'));
        $this->assertEquals('2.0', $request->getProtocolVersion());
    }

    public function testImmutabilityMultipleClones(): void
    {
        $request = new Request();
        $request1 = $request->withHeader('X-Header', 'value1');
        $request2 = $request->withHeader('X-Header', 'value2');
        $request3 = $request->withHeader('X-Header', 'value3');
        
        // Бүх clone-ууд тусдаа байх ёстой
        $this->assertNotSame($request1, $request2);
        $this->assertNotSame($request2, $request3);
        $this->assertNotSame($request1, $request3);
        
        // Анхны request өөрчлөгдөөгүй
        $this->assertFalse($request->hasHeader('X-Header'));
    }
}
