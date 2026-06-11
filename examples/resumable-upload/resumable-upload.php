<?php
/**
 * Example: Resumable Upload (CLI demo)
 *
 * Demonstrates using ResumableUploader to receive chunked uploads with
 * resume-on-failure support. Simulates a multi-chunk upload using local files.
 */
require_once __DIR__ . '/../../vendor/autoload.php';

use WebFiori\File\ResumableUploader;

$tmpDir = __DIR__ . '/../tmp';

if (!is_dir($tmpDir . '/uploads')) {
    mkdir($tmpDir . '/uploads', 0755, true);
}

// Create fake chunk files (simulates separate HTTP requests)
$chunk1 = $tmpDir . '/chunk1.dat';
$chunk2 = $tmpDir . '/chunk2.dat';
$chunk3 = $tmpDir . '/chunk3.dat';
file_put_contents($chunk1, str_repeat('A', 512));
file_put_contents($chunk2, str_repeat('B', 512));
file_put_contents($chunk3, str_repeat('C', 256));

$uploadId = 'demo-session-001';
$filename = 'assembled-file.dat';

// --- Chunk 1 ---
echo "=== Sending Chunk 1 ===\n";
$uploader = new ResumableUploader($tmpDir . '/uploads', [], $chunk1);
$result = $uploader->receiveChunk($uploadId, $filename, false);
echo "Offset after chunk 1: {$result['offset']} bytes\n";
echo "Complete: " . ($result['complete'] ? 'yes' : 'no') . "\n\n";

// --- Chunk 2 ---
echo "=== Sending Chunk 2 ===\n";
$uploader2 = new ResumableUploader($tmpDir . '/uploads', [], $chunk2);
$result2 = $uploader2->receiveChunk($uploadId, $filename, false);
echo "Offset after chunk 2: {$result2['offset']} bytes\n";
echo "Complete: " . ($result2['complete'] ? 'yes' : 'no') . "\n\n";

// --- Simulate failure: check offset to resume ---
echo "=== Simulating Resume Check ===\n";
$uploader3 = new ResumableUploader($tmpDir . '/uploads', [], $chunk3);
$offset = $uploader3->getOffset($uploadId, $filename);
echo "Server reports offset: {$offset} bytes (resume from here)\n\n";

// --- Chunk 3 (final) ---
echo "=== Sending Chunk 3 (final) ===\n";
$uploader3->setOnAfterUpload(function ($file) {
    echo "After-upload callback: {$file->getName()} saved.\n";
});
$result3 = $uploader3->receiveChunk($uploadId, $filename, true);
echo "Offset after chunk 3: {$result3['offset']} bytes\n";
echo "Complete: " . ($result3['complete'] ? 'yes' : 'no') . "\n";
echo "Final file: {$result3['file']->getName()}\n";
echo "Final size: " . filesize($result3['file']->getAbsolutePath()) . " bytes\n\n";

// --- Cleanup ---
echo "=== Cleanup ===\n";
$result3['file']->remove();
unlink($chunk1);
unlink($chunk2);
unlink($chunk3);
echo "Done.\n";
