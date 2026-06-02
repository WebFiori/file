<?php

/**
 * Example 6: Chunked Processing
 * 
 * Demonstrates splitting file data into fixed-size chunks. Useful for:
 * - Storing file data in database BLOB columns with size limits
 * - Transmitting large files over APIs in parts
 * - Progress tracking during file processing
 * 
 * Chunks can be returned as raw bytes or Base64-encoded strings.
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\File;

$file = new File();
$file->setRawData('ABCDEFGHIJ'); // 10 bytes

// Split into 3-byte raw chunks
$chunks = $file->getChunks(3, false);
echo "Raw chunks (".count($chunks)."):\n";

foreach ($chunks as $i => $chunk) {
    echo "  Chunk $i: '$chunk' (".strlen($chunk)." bytes)\n";
}
// Chunk 0: 'ABC' (3 bytes)
// Chunk 1: 'DEF' (3 bytes)
// Chunk 2: 'GHI' (3 bytes)
// Chunk 3: 'J'   (1 byte)

// Reassemble
$reassembled = implode('', $chunks);
echo "Reassembled: $reassembled\n"; // ABCDEFGHIJ

// Split into Base64-encoded chunks (useful for JSON APIs)
$encodedChunks = $file->getChunks(4, true);
echo "\nBase64 chunks (".count($encodedChunks)."):\n";

foreach ($encodedChunks as $i => $chunk) {
    echo "  Chunk $i: '$chunk'\n";
}

// Reassemble from encoded chunks
$decoded = base64_decode(implode('', $encodedChunks));
echo "Decoded: $decoded\n"; // ABCDEFGHIJ

// Negative chunk size defaults to 50 bytes
$defaultChunks = $file->getChunks(-1, false);
echo "\nDefault chunk size: ".count($defaultChunks)." chunk(s)\n"; // 1 (data < 50 bytes)
