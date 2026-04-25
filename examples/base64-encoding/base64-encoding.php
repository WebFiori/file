<?php
/**
 * Example 5: Base64 Encoding and Decoding
 * 
 * Demonstrates how to encode file data to Base64 and decode it back.
 * Useful for transmitting binary data over text-based protocols (APIs, email).
 * 
 * Also shows writeEncoded() / readDecoded() for persisting encoded files.
 */
require_once __DIR__ . '/../../vendor/autoload.php';

use WebFiori\File\File;
use WebFiori\File\Exceptions\FileException;

// --- In-memory encoding/decoding ---

$file = new File();
$file->setRawData('Binary data here');

// Get Base64 encoded version of the raw data
$encoded = $file->getRawData(true);
echo "Encoded: " . $encoded . "\n"; // QmluYXJ5IGRhdGEgaGVyZQ==

// getRawDataEncoded() does the same thing
echo "Same:    " . $file->getRawDataEncoded() . "\n";

// Decode Base64 data back into a new File object
$file2 = new File();
$file2->setRawData($encoded, true); // true = decode from Base64
echo "Decoded: " . $file2->getRawData() . "\n"; // Binary data here

// Strict mode: throws FileException if data contains characters outside Base64 alphabet
try {
    $file3 = new File();
    $file3->setRawData('not-valid!!!', true, true); // strict = true
} catch (FileException $e) {
    echo "Strict error: " . $e->getMessage() . "\n";
}

// --- File-based encoding/decoding ---

// writeEncoded() saves Base64-encoded content to a .bin file
$original = new File(__DIR__ . '/../tmp/secret.txt');
$original->create(true);
$original->setRawData('Confidential content');
$original->writeEncoded(); // Creates secret.txt.bin

// readDecoded() reads a Base64-encoded file and decodes it
$binFile = new File(__DIR__ . '/../tmp/secret.txt.bin');
$binFile->readDecoded();
echo "Restored: " . $binFile->getRawData() . "\n"; // Confidential content

// Cleanup
$original->remove();
$binFile->remove();
