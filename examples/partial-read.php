<?php
/**
 * Example 3: Partial Read (Byte Ranges)
 * 
 * Demonstrates how to read specific byte ranges from a file instead of
 * loading the entire content. Useful for large files or resumable downloads.
 */
require_once __DIR__ . '/../vendor/autoload.php';

use WebFiori\File\File;

// Create a sample file
$file = new File(__DIR__ . '/tmp/range-demo.txt');
$file->create(true);
$file->setRawData('ABCDEFGHIJ'); // 10 bytes, positions 0-9
$file->write(false);

// Read bytes from position 0 up to (not including) position 5
$file->read(0, 5);
echo "Bytes 0-5: " . $file->getRawData() . "\n"; // ABCDE

// Read bytes from position 3 up to position 7
$file->read(3, 7);
echo "Bytes 3-7: " . $file->getRawData() . "\n"; // DEFG

// Read the entire file (default behavior)
$file->read();
echo "Full read: " . $file->getRawData() . "\n"; // ABCDEFGHIJ

// Cleanup
$file->remove();
