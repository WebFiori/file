<?php

/**
 * Example: Creating a Custom ResponseEmitter
 *
 * Demonstrates how to implement the ResponseEmitter interface to integrate
 * file serving with any HTTP framework (PSR-7, Laravel, Symfony, etc.).
 *
 * This example creates:
 * 1. A BufferedEmitter that captures output for testing/inspection
 * 2. A LoggingEmitter that wraps another emitter with logging
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\FileStream;
use WebFiori\File\ResponseEmitter;

// --- Custom Emitter 1: BufferedEmitter ---
// Captures all output in memory instead of sending to the client.
// Useful for testing or transforming output before sending.

class BufferedEmitter implements ResponseEmitter {
    private int $statusCode = 200;
    private array $headers = [];
    private string $body = '';

    public function setHeader(string $name, string $value): void {
        $this->headers[$name] = $value;
    }

    public function setStatusCode(int $code): void {
        $this->statusCode = $code;
    }

    public function sendBody(\Generator $chunks): void {
        foreach ($chunks as $chunk) {
            $this->body .= $chunk;
        }
    }

    // Inspection methods
    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function getBody(): string {
        return $this->body;
    }
}

// --- Custom Emitter 2: LoggingEmitter ---
// Wraps another emitter, logging all operations. Demonstrates the decorator pattern.

class LoggingEmitter implements ResponseEmitter {
    private ResponseEmitter $inner;
    private array $log = [];

    public function __construct(ResponseEmitter $inner) {
        $this->inner = $inner;
    }

    public function setHeader(string $name, string $value): void {
        $this->log[] = "Header: $name: $value";
        $this->inner->setHeader($name, $value);
    }

    public function setStatusCode(int $code): void {
        $this->log[] = "Status: $code";
        $this->inner->setStatusCode($code);
    }

    public function sendBody(\Generator $chunks): void {
        $this->log[] = "Body: sending...";
        $this->inner->sendBody($chunks);
        $this->log[] = "Body: done";
    }

    public function getLog(): array {
        return $this->log;
    }
}

// --- Demo ---

$tmpDir = __DIR__.'/../tmp';

if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0755, true);
}

$samplePath = $tmpDir.'/emitter-demo.txt';
file_put_contents($samplePath, "Line 1: Hello from custom emitter.\nLine 2: This is streamed.\n");

// --- Using BufferedEmitter ---
echo "=== BufferedEmitter ===\n";

$buffered = new BufferedEmitter();
$stream = new FileStream($samplePath);
$stream->serve(false, $buffered);

echo "Status: ".$buffered->getStatusCode()."\n";
echo "Headers:\n";

foreach ($buffered->getHeaders() as $name => $value) {
    echo "  $name: $value\n";
}

echo "Body length: ".strlen($buffered->getBody())." bytes\n";
echo "Body:\n  ".$buffered->getBody()."\n";

// --- Using LoggingEmitter (wrapping BufferedEmitter) ---
echo "=== LoggingEmitter ===\n";

$inner = new BufferedEmitter();
$logging = new LoggingEmitter($inner);
$stream2 = new FileStream($samplePath);
$stream2->serve(true, $logging); // true = attachment

echo "Log entries:\n";

foreach ($logging->getLog() as $entry) {
    echo "  $entry\n";
}

echo "\nCaptured body: ".strlen($inner->getBody())." bytes\n";

// Cleanup
unlink($samplePath);
