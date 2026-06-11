<?php

/**
 * Example: Streaming File I/O
 * 
 * Demonstrates using FileStream for constant-memory file operations:
 * reading chunks, reading lines, reading byte ranges, and serving files.
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\File;
use WebFiori\File\FileStream;

// Create a sample file
$samplePath = __DIR__.'/../tmp/stream-demo.txt';
file_put_contents($samplePath, "Line 1: Hello\nLine 2: World\nLine 3: Stream\nLine 4: Demo\n");

// --- Reading in chunks (constant memory) ---
echo "=== Read Chunks (10 bytes each) ===\n";
$stream = new FileStream($samplePath);
$chunkNum = 0;

foreach ($stream->readChunks(10) as $chunk) {
    $chunkNum++;
    echo "Chunk $chunkNum: ".json_encode($chunk)."\n";
}

// --- Reading line by line ---
echo "\n=== Read Lines ===\n";
foreach ($stream->readLines() as $line) {
    echo "  ".rtrim($line)."\n";
}

// --- Reading a byte range ---
echo "\n=== Read Range (bytes 0-13) ===\n";
$data = '';
foreach ($stream->readRange(0, 13) as $chunk) {
    $data .= $chunk;
}
echo "  ".$data."\n"; // "Line 1: Hello"

// --- File metadata ---
echo "\n=== Metadata ===\n";
echo "Name: ".$stream->getName()."\n";
echo "MIME: ".$stream->getMIME()."\n";
echo "Size: ".$stream->getSize()." bytes\n";

// --- Bridge from File class ---
echo "\n=== Bridge from File::stream() ===\n";
$file = new File($samplePath);
$fileStream = $file->stream(16); // 16-byte buffer
echo "Buffer size: ".$fileStream->getBufferSize()."\n";

// --- Writing with a generator (writeFromStream) ---
echo "\n=== writeFromStream() ===\n";
$outputPath = __DIR__.'/../tmp/stream-output.txt';

$generator = (function () {
    yield "Generated line 1\n";
    yield "Generated line 2\n";
    yield "Generated line 3\n";
})();

$output = new FileStream($outputPath);
$output->writeFromStream($generator, false); // false = overwrite mode
echo "Written to: ".$output->getName()." (".$output->getSize()." bytes)\n";

// Read it back to verify
foreach ($output->readLines() as $line) {
    echo "  ".rtrim($line)."\n";
}

// Cleanup
unlink($samplePath);
unlink($outputPath);
