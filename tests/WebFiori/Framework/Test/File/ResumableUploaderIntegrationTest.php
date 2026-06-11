<?php

namespace WebFiori\Framework\Test\File;

use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class ResumableUploaderIntegrationTest extends TestCase {
    private static $serverProcess;
    private static int $port;
    private static string $baseUrl;
    private static string $uploadDir;

    public static function setUpBeforeClass(): void {
        self::$port = self::findAvailablePort();
        self::$baseUrl = 'http://localhost:' . self::$port;
        self::$uploadDir = ROOT_PATH . 'tests' . DS . 'tmp' . DS . 'integration-uploads';
        $serverScript = ROOT_PATH . 'tests' . DS . 'integration-server.php';

        self::$serverProcess = proc_open(
            ['php', '-S', 'localhost:' . self::$port, $serverScript],
            [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
            $pipes
        );

        if (!is_resource(self::$serverProcess)) {
            self::fail('Failed to start test server.');
        }

        // Wait for server to accept connections
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

        // Clean up upload directory
        if (is_dir(self::$uploadDir)) {
            $partialDir = self::$uploadDir . DS . '.partial';

            if (is_dir($partialDir)) {
                foreach (glob($partialDir . DS . '*') as $f) {
                    @unlink($f);
                }
                @rmdir($partialDir);
            }

            foreach (glob(self::$uploadDir . DS . '*') as $f) {
                @unlink($f);
            }
            @rmdir(self::$uploadDir);
        }
    }

    /**
     * @test
     */
    public function testSingleChunkUpload(): void {
        $uploadId = 'int-single-' . uniqid();
        $filename = 'single.bin';
        $data = str_repeat('S', 256);

        $result = $this->postChunk($data, $uploadId, $filename, true);

        $this->assertTrue($result['complete']);
        $this->assertEquals(256, $result['offset']);
        $this->assertEquals($filename, $result['filename']);
        $this->assertEquals(256, $result['size']);
    }

    /**
     * @test
     */
    public function testMultiChunkUpload(): void {
        $uploadId = 'int-multi-' . uniqid();
        $filename = 'multi.bin';

        $result1 = $this->postChunk(str_repeat('A', 100), $uploadId, $filename, false);
        $this->assertFalse($result1['complete']);
        $this->assertEquals(100, $result1['offset']);

        $result2 = $this->postChunk(str_repeat('B', 100), $uploadId, $filename, false);
        $this->assertFalse($result2['complete']);
        $this->assertEquals(200, $result2['offset']);

        $result3 = $this->postChunk(str_repeat('C', 50), $uploadId, $filename, true);
        $this->assertTrue($result3['complete']);
        $this->assertEquals(250, $result3['size']);
    }

    /**
     * @test
     */
    public function testGetOffsetForResume(): void {
        $uploadId = 'int-resume-' . uniqid();
        $filename = 'resume.bin';

        // Initially offset is 0
        $this->assertEquals(0, $this->getOffset($uploadId, $filename));

        // Send a chunk
        $this->postChunk(str_repeat('R', 300), $uploadId, $filename, false);

        // Offset should reflect what was received
        $this->assertEquals(300, $this->getOffset($uploadId, $filename));

        // Send another chunk
        $this->postChunk(str_repeat('S', 200), $uploadId, $filename, false);
        $this->assertEquals(500, $this->getOffset($uploadId, $filename));

        // Finalize
        $result = $this->postChunk(str_repeat('T', 100), $uploadId, $filename, true);
        $this->assertTrue($result['complete']);
        $this->assertEquals(600, $result['size']);
    }

    /**
     * @test
     */
    public function testEmptyUploadIdReturnsError(): void {
        $ctx = stream_context_create(['http' => [
            'method' => 'POST',
            'header' => implode("\r\n", [
                'Content-Type: application/octet-stream',
                'X-Upload-Id: ',
                'X-Filename: test.bin',
                'X-Is-Last: false',
            ]),
            'content' => 'data',
            'ignore_errors' => true,
        ]]);

        $response = file_get_contents(self::$baseUrl . '/server.php', false, $ctx);
        $data = json_decode($response, true);
        $this->assertArrayHasKey('error', $data);
    }

    /**
     * @test
     */
    public function testResumeAfterSimulatedFailure(): void {
        $uploadId = 'int-fail-' . uniqid();
        $filename = 'failtest.bin';
        $fullData = str_repeat('X', 1000);

        // Send first half
        $this->postChunk(substr($fullData, 0, 500), $uploadId, $filename, false);

        // "Failure" happens — client reconnects and checks offset
        $offset = $this->getOffset($uploadId, $filename);
        $this->assertEquals(500, $offset);

        // Resume from offset
        $result = $this->postChunk(substr($fullData, $offset), $uploadId, $filename, true);
        $this->assertTrue($result['complete']);
        $this->assertEquals(1000, $result['size']);
    }

    private function postChunk(string $body, string $uploadId, string $filename, bool $isLast): array {
        $ctx = stream_context_create(['http' => [
            'method' => 'POST',
            'header' => implode("\r\n", [
                'Content-Type: application/octet-stream',
                'X-Upload-Id: ' . $uploadId,
                'X-Filename: ' . $filename,
                'X-Is-Last: ' . ($isLast ? 'true' : 'false'),
            ]),
            'content' => $body,
            'ignore_errors' => true,
        ]]);

        $response = file_get_contents(self::$baseUrl . '/server.php', false, $ctx);

        return json_decode($response, true);
    }

    private function getOffset(string $uploadId, string $filename): int {
        $url = self::$baseUrl . '/server.php?uploadId=' . urlencode($uploadId) . '&filename=' . urlencode($filename);
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        return $data['offset'];
    }

    private static function findAvailablePort(): int {
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($sock, '127.0.0.1', 0);
        socket_getsockname($sock, $addr, $port);
        socket_close($sock);

        return $port;
    }
}
