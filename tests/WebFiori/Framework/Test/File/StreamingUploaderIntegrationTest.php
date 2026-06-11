<?php

namespace WebFiori\Framework\Test\File;

use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class StreamingUploaderIntegrationTest extends TestCase {
    private static $serverProcess;
    private static int $port;
    private static string $baseUrl;
    private static string $uploadDir;

    public static function setUpBeforeClass(): void {
        self::$port = self::findAvailablePort();
        self::$baseUrl = 'http://localhost:' . self::$port;
        self::$uploadDir = ROOT_PATH . 'tests' . DS . 'tmp' . DS . 'integration-uploads';
        $serverScript = ROOT_PATH . 'tests' . DS . 'integration-streaming-server.php';

        self::$serverProcess = proc_open(
            ['php', '-S', 'localhost:' . self::$port, $serverScript],
            [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
            $pipes
        );

        if (!is_resource(self::$serverProcess)) {
            self::fail('Failed to start test server.');
        }

        $deadline = time() + 5;

        while (time() < $deadline) {
            $conn = @fsockopen('localhost', self::$port, $errno, $errstr, 0.1);

            if ($conn) {
                fclose($conn);

                return;
            }
            usleep(50000);
        }

        self::fail('Test server did not start within 5 seconds.');
    }

    public static function tearDownAfterClass(): void {
        if (is_resource(self::$serverProcess)) {
            proc_terminate(self::$serverProcess);
            proc_close(self::$serverProcess);
        }

        if (is_dir(self::$uploadDir)) {
            foreach (glob(self::$uploadDir . DS . '*') as $f) {
                @unlink($f);
            }
            @rmdir(self::$uploadDir);
        }
    }

    /**
     * @test
     */
    public function testBasicUpload(): void {
        $data = str_repeat('Hello', 100);
        $result = $this->post($data, 'hello.txt');

        $this->assertEquals('hello.txt', $result['filename']);
        $this->assertEquals(500, $result['size']);
        $this->assertTrue($result['uploaded']);
    }

    /**
     * @test
     */
    public function testLargeUpload(): void {
        $data = str_repeat('X', 1024 * 100); // 100KB
        $result = $this->post($data, 'large.bin');

        $this->assertEquals('large.bin', $result['filename']);
        $this->assertEquals(102400, $result['size']);
    }

    /**
     * @test
     */
    public function testFilenameFromHeader(): void {
        $result = $this->post('content', 'from-header.dat');
        $this->assertEquals('from-header.dat', $result['filename']);
    }

    /**
     * @test
     */
    public function testSizeLimitRejection(): void {
        $data = str_repeat('Z', 200);
        $result = $this->post($data, 'toobig.bin', ['X-Max-Size: 50']);

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('size limit', $result['error']);
    }

    /**
     * @test
     */
    public function testExtensionRejection(): void {
        $result = $this->post('data', 'bad.exe', ['X-Allowed-Exts: txt,pdf']);

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('type not allowed', $result['error']);
    }

    /**
     * @test
     */
    public function testAllowedExtension(): void {
        $result = $this->post('data', 'good.txt', ['X-Allowed-Exts: txt,pdf']);

        $this->assertEquals('good.txt', $result['filename']);
        $this->assertTrue($result['uploaded']);
    }

    private function post(string $body, string $filename, array $extraHeaders = []): array {
        $headers = array_merge([
            'Content-Type: application/octet-stream',
            'X-Filename: ' . $filename,
        ], $extraHeaders);

        $ctx = stream_context_create(['http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => $body,
            'ignore_errors' => true,
        ]]);

        $response = file_get_contents(self::$baseUrl, false, $ctx);

        return json_decode($response, true);
    }

    private static function findAvailablePort(): int {
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($sock, '127.0.0.1', 0);
        socket_getsockname($sock, $addr, $port);
        socket_close($sock);

        return $port;
    }
}
