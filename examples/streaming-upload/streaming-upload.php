<?php

/**
 * Example: Streaming Upload (php://input)
 * 
 * Demonstrates using StreamingUploader to receive files from raw HTTP body
 * in constant memory, with hash verification.
 * 
 * In production, the client sends:
 *   fetch('/upload', {
 *       method: 'POST',
 *       headers: { 'Content-Type': 'application/octet-stream', 'X-Filename': 'video.mp4' },
 *       body: file
 *   });
 * 
 * For this CLI demo, we simulate the input with a local file.
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\StreamingUploader;

$tmpDir = __DIR__.'/../tmp';

if (!is_dir($tmpDir.'/uploads')) {
    mkdir($tmpDir.'/uploads', 0755, true);
}

// Create a fake input file (simulates php://input)
$inputPath = $tmpDir.'/fake-input.dat';
file_put_contents($inputPath, str_repeat('A', 1024).'END'); // 1027 bytes

// --- Basic streaming upload ---
echo "=== Basic Upload ===\n";
$uploader = new StreamingUploader($tmpDir.'/uploads', ['dat', 'bin'], $inputPath);
$file = $uploader->receive('streamed-file.dat');
echo "Uploaded: ".$file->getName()."\n";
echo "Size:     ".filesize($file->getAbsolutePath())." bytes\n";
$file->remove();

// --- With hash verification ---
echo "\n=== Upload with Hash Verification ===\n";
$uploader2 = new StreamingUploader($tmpDir.'/uploads', [], $inputPath);
$checksum = null;

$uploader2->setStreamProcessor(function(\Generator $chunks, string $destPath) use (&$checksum) {
    $hash = hash_init('sha256');
    $dest = fopen($destPath, 'wb');

    foreach ($chunks as $chunk) {
        hash_update($hash, $chunk);
        fwrite($dest, $chunk);
    }

    fclose($dest);
    $checksum = hash_final($hash);
});

$file2 = $uploader2->receive('verified.bin');
echo "Uploaded: ".$file2->getName()."\n";
echo "SHA-256:  ".$checksum."\n";
$file2->remove();

// --- With size limit ---
echo "\n=== Upload with Size Limit (reject) ===\n";
$uploader3 = new StreamingUploader($tmpDir.'/uploads', [], $inputPath);
$uploader3->setMaxFileSize(100); // Only allow 100 bytes

try {
    $uploader3->receive('too-large.bin');
} catch (\WebFiori\File\Exceptions\FileException $e) {
    echo "Rejected: ".$e->getMessage()."\n";
}

// Cleanup
unlink($inputPath);
@rmdir($tmpDir.'/uploads');
