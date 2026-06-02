<?php

/**
 * Example 7: Binary Representation (Bytes and Hex Arrays)
 * 
 * Demonstrates converting file data to byte arrays and hex arrays.
 * Useful for binary analysis, checksums, or low-level data inspection.
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\File;

$file = new File();
$file->setRawData('Hello');

// Get as array of integers (0-255), one per byte
$bytes = $file->toBytesArray();
echo "Bytes: ".implode(', ', $bytes)."\n"; // 72, 101, 108, 108, 111

// Get as array of two-character hex strings
$hex = $file->toHexArray();
echo "Hex:   ".implode(' ', $hex)."\n"; // 48 65 6C 6C 6F

// Round-trip: convert hex back to original string
$restored = hex2bin(implode($hex));
echo "Restored: $restored\n"; // Hello

// Works with Unicode too
$file->setRawData('مرحبا');
$hexUnicode = $file->toHexArray();
echo "\nUnicode hex: ".implode(' ', $hexUnicode)."\n";
echo "Restored: ".hex2bin(implode($hexUnicode))."\n"; // مرحبا
