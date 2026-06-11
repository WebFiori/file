<?php

/**
 * Example: Serving Files over HTTP
 * 
 * Demonstrates serving files using File::view() and FileStream::serve()
 * with ResponseEmitter for framework-agnostic HTTP output.
 * 
 * Run as web server:
 *   php -S localhost:8080 examples/serving-files/router.php
 * 
 * Then visit:
 *   http://localhost:8080/inline    — View file inline in browser
 *   http://localhost:8080/download  — Download as attachment
 *   http://localhost:8080/stream    — Serve via FileStream (constant memory)
 * 
 * Run as CLI demo (shows what would be served):
 *   php examples/serving-files/serving-files.php
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\File;
use WebFiori\File\FileStream;

$tmpDir = __DIR__.'/../tmp';
$samplePath = $tmpDir.'/serve-demo.txt';

if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0755, true);
}

file_put_contents($samplePath, "This file is served by the WebFiori File library.\nLine 2: Streaming works for any file size.\n");

// --- CLI Mode (no web server) ---
echo "=== File::view() ===\n";
$file = new File($samplePath);
$file->read();
echo "Name: ".$file->getName()."\n";
echo "MIME: ".$file->getMIME()."\n";
echo "Size: ".$file->getSize()." bytes\n";
echo "Content: ".$file->getRawData()."\n";

echo "\n=== FileStream::serve() ===\n";
$stream = new FileStream($samplePath);
echo "Name: ".$stream->getName()."\n";
echo "MIME: ".$stream->getMIME()."\n";
echo "Size: ".$stream->getSize()." bytes\n";
echo "Buffer: ".$stream->getBufferSize()." bytes\n";

// Show what headers would be sent
echo "\nHeaders that would be sent:\n";
echo "  Content-Type: ".$stream->getMIME()."\n";
echo "  Content-Length: ".$stream->getSize()."\n";
echo "  Content-Disposition: inline; filename=\"".$stream->getName()."\"\n";
echo "  Accept-Ranges: bytes\n";

// Cleanup
unlink($samplePath);
