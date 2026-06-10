<?php

/**
 * Example: Atomic Write
 * 
 * Demonstrates using writeAtomic() for crash-safe file writes.
 * Data is written to a temp file first, then atomically renamed.
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\FileStream;

$tmpDir = __DIR__.'/../tmp';
$targetPath = $tmpDir.'/config.json';

// Create initial file
file_put_contents($targetPath, '{"version": 1}');
echo "Before: ".file_get_contents($targetPath)."\n";

// --- Atomic write from an array ---
$stream = new FileStream($targetPath);
$stream->writeAtomic(['{"version": 2, "updated": true}']);
echo "After:  ".file_get_contents($targetPath)."\n";

// --- Atomic write by streaming from another file ---
$sourcePath = $tmpDir.'/new-config.json';
file_put_contents($sourcePath, '{"version": 3, "streamed": true}');

$source = new FileStream($sourcePath);
$target = new FileStream($targetPath);
$target->writeAtomic($source->readChunks());
echo "Final:  ".file_get_contents($targetPath)."\n";

// Cleanup
unlink($targetPath);
unlink($sourcePath);
