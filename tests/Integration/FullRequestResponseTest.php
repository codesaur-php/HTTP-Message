<?php

namespace codesaur\Http\Message\Tests\Integration;

use PHPUnit\Framework\TestCase;

use codesaur\Http\Message\ServerRequest;
use codesaur\Http\Message\Response;
use codesaur\Http\Message\Request;
use codesaur\Http\Message\Uri;
use codesaur\Http\Message\Stream;
use codesaur\Http\Message\UploadedFile;

/**
 * Integration тестүүд - бүх компонентуудыг хамтдаа ашиглах
 */
class FullRequestResponseTest extends TestCase
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

    public function testFullRequestResponseCycle(): void
    {
        // 1. Request үүсгэх
        $uri = new Uri();
        $uri->setScheme('https');
        $uri->setHost('api.example.com');
        $uri->setPath('/users');
        $uri->setQuery('page=1&limit=10');
        
        $request = new Request();
        $request = $request
            ->withMethod('GET')
            ->withUri($uri)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer token123')
            ->withProtocolVersion('2.0');
        
        // 2. Request-ийн мэдээллийг шалгах
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('https://api.example.com/users?page=1&limit=10', (string) $request->getUri());
        $this->assertEquals('application/json', $request->getHeaderLine('Accept'));
        $this->assertEquals('Bearer token123', $request->getHeaderLine('Authorization'));
        $this->assertEquals('2.0', $request->getProtocolVersion());
        
        // 3. Response үүсгэх
        $response = new Response();
        $response = $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('X-Request-ID', 'req-123');
        
        // 4. Response body-д мэдээлэл бичих
        $data = ['users' => [['id' => 1, 'name' => 'John']], 'total' => 1];
        $response->getBody()->write(json_encode($data));
        
        // 5. Response-ийн мэдээллийг шалгах
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('req-123', $response->getHeaderLine('X-Request-ID'));
        
        $bodyContent = (string) $response->getBody();
        $decoded = json_decode($bodyContent, true);
        $this->assertEquals($data, $decoded);
    }

    public function testRequestWithBodyStream(): void
    {
        // Request body-д мэдээлэл бичих
        $request = new Request();
        $request = $request->withMethod('POST');
        
        $bodyData = ['name' => 'John Doe', 'email' => 'john@example.com'];
        $request->getBody()->write(json_encode($bodyData));
        
        // Body-г унших
        $request->getBody()->rewind();
        $content = $request->getBody()->getContents();
        $decoded = json_decode($content, true);
        
        $this->assertEquals($bodyData, $decoded);
    }

    public function testResponseWithCustomBody(): void
    {
        // Custom Stream body ашиглах
        $resource = fopen('php://temp', 'r+');
        $customBody = new Stream($resource);
        $customBody->write('Custom body content');
        $customBody->rewind(); // Rewind хийх хэрэгтэй
        
        $response = new Response();
        $response = $response->withBody($customBody);
        
        $this->assertSame($customBody, $response->getBody());
        $this->assertEquals('Custom body content', $response->getBody()->getContents());
    }

    public function testFileUploadWorkflow(): void
    {
        // 1. Түр файл үүсгэх
        $tmpFile = sys_get_temp_dir() . '/upload_test_' . uniqid() . '.txt';
        $content = 'Test file content for upload';
        file_put_contents($tmpFile, $content);
        
        // 2. UploadedFile үүсгэх
        $uploadedFile = new UploadedFile(
            $tmpFile,
            'test.txt',
            'text/plain',
            strlen($content),
            UPLOAD_ERR_OK
        );
        
        // 3. Файлын мэдээллийг шалгах
        $this->assertEquals('test.txt', $uploadedFile->getClientFilename());
        $this->assertEquals('text/plain', $uploadedFile->getClientMediaType());
        $this->assertEquals(strlen($content), $uploadedFile->getSize());
        $this->assertEquals(UPLOAD_ERR_OK, $uploadedFile->getError());
        
        // 4. Файлыг зөөх
        $targetPath = sys_get_temp_dir() . '/moved_' . uniqid() . '.txt';
        $uploadedFile->moveTo($targetPath);
        
        // 5. Зөөгдсөн файлыг шалгах
        $this->assertFileExists($targetPath);
        $this->assertEquals($content, file_get_contents($targetPath));
        
        // 6. Түр файл байхгүй байх ёстой (CLI mode)
        if (PHP_SAPI === 'cli') {
            $this->assertFileDoesNotExist($tmpFile);
        }
        
        // Cleanup
        if (file_exists($targetPath)) {
            unlink($targetPath);
        }
    }

    public function testServerRequestWithUploadedFiles(): void
    {
        // 1. Түр файлууд үүсгэх
        $tmpFile1 = sys_get_temp_dir() . '/file1_' . uniqid() . '.txt';
        $tmpFile2 = sys_get_temp_dir() . '/file2_' . uniqid() . '.txt';
        file_put_contents($tmpFile1, 'Content 1');
        file_put_contents($tmpFile2, 'Content 2');
        
        // 2. UploadedFile-үүд үүсгэх
        $file1 = new UploadedFile($tmpFile1, 'file1.txt', 'text/plain', 9, UPLOAD_ERR_OK);
        $file2 = new UploadedFile($tmpFile2, 'file2.txt', 'text/plain', 9, UPLOAD_ERR_OK);
        
        // 3. ServerRequest үүсгэх
        $request = new ServerRequest();
        $request = $request->withUploadedFiles([
            'avatar' => $file1,
            'documents' => [
                'primary' => $file2
            ]
        ]);
        
        // 4. Uploaded files-ийг шалгах
        $files = $request->getUploadedFiles();
        $this->assertInstanceOf(UploadedFile::class, $files['avatar']);
        $this->assertInstanceOf(UploadedFile::class, $files['documents']['primary']);
        
        // Cleanup
        unlink($tmpFile1);
        unlink($tmpFile2);
    }

    public function testRequestResponseWithCookies(): void
    {
        // 1. ServerRequest cookies-тэй
        $request = new ServerRequest();
        $request = $request->withCookieParams([
            'session_id' => 'abc123',
            'user_pref' => 'dark_mode',
            'lang' => 'mn'
        ]);
        
        $cookies = $request->getCookieParams();
        $this->assertEquals('abc123', $cookies['session_id']);
        $this->assertEquals('dark_mode', $cookies['user_pref']);
        $this->assertEquals('mn', $cookies['lang']);
        
        // 2. Response-д Set-Cookie header нэмэх
        $response = new Response();
        $response = $response->withHeader('Set-Cookie', 'session_id=abc123; Path=/; HttpOnly');
        $response = $response->withAddedHeader('Set-Cookie', 'lang=mn; Path=/');
        
        $cookies = $response->getHeader('Set-Cookie');
        $this->assertCount(2, $cookies);
    }

    public function testRequestResponseWithAttributes(): void
    {
        // 1. ServerRequest attributes-тэй
        $request = new ServerRequest();
        $request = $request
            ->withAttribute('route', 'users.show')
            ->withAttribute('user_id', 123)
            ->withAttribute('middleware', ['auth', 'throttle']);
        
        // 2. Attributes-ийг шалгах
        $this->assertEquals('users.show', $request->getAttribute('route'));
        $this->assertEquals(123, $request->getAttribute('user_id'));
        $this->assertEquals(['auth', 'throttle'], $request->getAttribute('middleware'));
        
        // 3. Attribute устгах
        $request = $request->withoutAttribute('middleware');
        $this->assertNull($request->getAttribute('middleware'));
        $this->assertEquals('users.show', $request->getAttribute('route'));
    }

    public function testComplexUriBuilding(): void
    {
        // 1. Нарийн URI үүсгэх
        $uri = new Uri();
        $uri->setScheme('https');
        $uri->setUserInfo('user', 'pass:word');
        $uri->setHost('api.example.com');
        $uri->setPort(8443);
        $uri->setPath('/api/v1/users/123');
        $uri->setQuery('include=posts,comments&fields=id,name,email');
        $uri->setFragment('profile');
        
        // 2. URI-г шалгах
        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('user:pass:word', $uri->getUserInfo());
        $this->assertEquals('api.example.com', $uri->getHost());
        $this->assertEquals(8443, $uri->getPort());
        $this->assertEquals('/api/v1/users/123', $uri->getPath());
        $this->assertEquals('include=posts,comments&fields=id,name,email', $uri->getQuery());
        $this->assertEquals('profile', $uri->getFragment());
        
        // 3. Request-д ашиглах
        $request = new Request();
        $request = $request->withUri($uri)->withMethod('GET');
        
        // User info-д : тэмдэгт байвал зөвхөн password-ийг encode хийнэ
        // user:pass:word → user:pass%3Aword (зөвхөн password дахь : encode хийгдэнэ)
        $expectedUri = 'https://user:pass%3Aword@api.example.com:8443/api/v1/users/123?include=posts,comments&fields=id,name,email#profile';
        $this->assertEquals($expectedUri, (string) $request->getUri());
    }

    public function testJSONRequestResponse(): void
    {
        // 1. JSON request body
        $request = new Request();
        $request = $request
            ->withMethod('POST')
            ->withHeader('Content-Type', 'application/json');
        
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 30
        ];
        $request->getBody()->write(json_encode($requestData));
        
        // 2. Request body-г parse хийх
        $request->getBody()->rewind();
        $bodyContent = $request->getBody()->getContents();
        $decoded = json_decode($bodyContent, true);
        $this->assertEquals($requestData, $decoded);
        
        // 3. JSON response
        $response = new Response();
        $response = $response
            ->withStatus(201)
            ->withHeader('Content-Type', 'application/json');
        
        $responseData = [
            'id' => 123,
            'name' => 'John Doe',
            'created_at' => '2024-01-01T00:00:00Z'
        ];
        $response->getBody()->write(json_encode($responseData));
        
        // 4. Response body-г шалгах
        $responseBody = (string) $response->getBody();
        $responseDecoded = json_decode($responseBody, true);
        $this->assertEquals($responseData, $responseDecoded);
    }

    public function testRedirectResponse(): void
    {
        // 1. Redirect response үүсгэх
        $response = new Response();
        $response = $response
            ->withStatus(301)
            ->withHeader('Location', 'https://example.com/new-url');
        
        // 2. Redirect мэдээллийг шалгах
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('Moved Permanently', $response->getReasonPhrase());
        $this->assertEquals('https://example.com/new-url', $response->getHeaderLine('Location'));
    }

    public function testErrorResponse(): void
    {
        // 1. Error response үүсгэх
        $response = new Response();
        $response = $response
            ->withStatus(404)
            ->withHeader('Content-Type', 'application/json');
        
        $errorData = [
            'error' => 'Not Found',
            'message' => 'The requested resource was not found',
            'code' => 404
        ];
        $response->getBody()->write(json_encode($errorData));
        
        // 2. Error response-ийг шалгах
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Not Found', $response->getReasonPhrase());
        
        $bodyContent = (string) $response->getBody();
        $decoded = json_decode($bodyContent, true);
        $this->assertEquals($errorData, $decoded);
    }

    public function testFullWorkflowWithQueryParams(): void
    {
        // 1. Query параметртэй request
        $uri = new Uri();
        $uri->setPath('/api/search');
        $uri->setQuery('q=test&page=1&limit=20&sort=name&order=asc');
        
        $request = new Request();
        $request = $request->withUri($uri);
        
        // 2. Query параметрүүдийг parse хийх (ServerRequest-д)
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withQueryParams([
            'q' => 'test',
            'page' => '1',
            'limit' => '20',
            'sort' => 'name',
            'order' => 'asc'
        ]);
        
        $params = $serverRequest->getQueryParams();
        $this->assertEquals('test', $params['q']);
        $this->assertEquals('1', $params['page']);
        $this->assertEquals('20', $params['limit']);
        
        // 3. Response-д pagination мэдээлэл
        $response = new Response();
        $response = $response->withHeader('X-Total-Count', '100');
        $response = $response->withHeader('X-Page', '1');
        $response = $response->withHeader('X-Per-Page', '20');
        
        $this->assertEquals('100', $response->getHeaderLine('X-Total-Count'));
        $this->assertEquals('1', $response->getHeaderLine('X-Page'));
    }
}
