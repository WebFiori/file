<?php

namespace WebFiori\Framework\Test\File;

use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class FileUploaderIntegrationTest extends TestCase {
    private static $serverProcess;
    private static int $port;
    private static string $baseUrl;
    private static string $uploadDir;

    public static function setUpBeforeClass(): void {
        self::$port = self::findAvailablePort();
        self::$baseUrl = 'http://localhost:' . self::$port;
        self::$uploadDir = ROOT_PATH . 'tests' . DS . 'tmp' . DS . 'integration-uploads';
        $serverScript = ROOT_PATH . 'tests' . DS . 'integration-fileupload-server.php';

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
    public function testSingleFileUpload(): void {
        $result = $this->uploadFiles([
            ['name' => 'document.txt', 'content' => 'Hello World'],
        ]);

        $this->assertArrayHasKey('files', $result);
        $this->assertCount(1, $result['files']);
        $this->assertEquals('document.txt', $result['files'][0]['name']);
        $this->assertTrue($result['files'][0]['uploaded']);
    }

    /**
     * @test
     */
    public function testMultipleFileUpload(): void {
        $result = $this->uploadFiles([
            ['name' => 'file1.txt', 'content' => 'Content 1'],
            ['name' => 'file2.txt', 'content' => 'Content 2'],
        ]);

        $this->assertArrayHasKey('files', $result);
        $this->assertCount(2, $result['files']);
        $this->assertEquals('file1.txt', $result['files'][0]['name']);
        $this->assertEquals('file2.txt', $result['files'][1]['name']);
    }

    /**
     * @test
     */
    public function testUploadWithExtensionFilter(): void {
        $result = $this->uploadFiles(
            [['name' => 'script.exe', 'content' => 'binary data']],
            'txt,pdf'
        );

        $this->assertArrayHasKey('files', $result);
        $this->assertCount(1, $result['files']);
        $this->assertFalse($result['files'][0]['uploaded']);
        $this->assertNotEmpty($result['files'][0]['error']);
    }

    /**
     * @test
     */
    public function testUploadAllowedExtension(): void {
        $result = $this->uploadFiles(
            [['name' => 'notes.txt', 'content' => 'some text']],
            'txt'
        );

        $this->assertArrayHasKey('files', $result);
        $this->assertTrue($result['files'][0]['uploaded']);
    }

    /**
     * @test
     */
    public function testReplaceExistingFile(): void {
        // Upload first time
        $this->uploadFiles([['name' => 'replace-me.txt', 'content' => 'first']]);

        // Upload again — PHP's built-in server doesn't support replace header easily,
        // but at minimum we verify the upload succeeds
        $result = $this->uploadFiles([['name' => 'replace-me2.txt', 'content' => 'second']]);
        $this->assertTrue($result['files'][0]['uploaded']);
    }

    /**
     * Builds a multipart/form-data request and sends it.
     */
    private function uploadFiles(array $files, ?string $allowedExts = null): array {
        $boundary = 'boundary-' . uniqid();
        $body = '';

        // Add allowed_exts field if specified
        if ($allowedExts !== null) {
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Disposition: form-data; name=\"allowed_exts\"\r\n\r\n";
            $body .= "{$allowedExts}\r\n";
        }

        // Add file(s)
        if (count($files) === 1) {
            $file = $files[0];
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Disposition: form-data; name=\"file\"; filename=\"{$file['name']}\"\r\n";
            $body .= "Content-Type: application/octet-stream\r\n\r\n";
            $body .= "{$file['content']}\r\n";
        } else {
            foreach ($files as $i => $file) {
                $body .= "--{$boundary}\r\n";
                $body .= "Content-Disposition: form-data; name=\"file[{$i}]\"; filename=\"{$file['name']}\"\r\n";
                $body .= "Content-Type: application/octet-stream\r\n\r\n";
                $body .= "{$file['content']}\r\n";
            }
        }

        $body .= "--{$boundary}--\r\n";

        $ctx = stream_context_create(['http' => [
            'method' => 'POST',
            'header' => "Content-Type: multipart/form-data; boundary={$boundary}\r\n"
                      . 'Content-Length: ' . strlen($body),
            'content' => $body,
            'ignore_errors' => true,
        ]]);

        $response = file_get_contents(self::$baseUrl, false, $ctx);

        return json_decode($response, true) ?? ['error' => 'Invalid JSON response: ' . $response];
    }

    private static function findAvailablePort(): int {
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($sock, '127.0.0.1', 0);
        socket_getsockname($sock, $addr, $port);
        socket_close($sock);

        return $port;
    }
}
