<?php
/**
 * Streaming Upload Server
 * 
 * Receives a single file upload from the raw HTTP body using StreamingUploader.
 * The file is written to disk in constant memory.
 * 
 * Run: php -S localhost:8080 examples/streaming-upload/router.php
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\Exceptions\FileException;
use WebFiori\File\StreamingUploader;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Filename');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$uploadDir = __DIR__.'/../tmp/stream-uploads';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

try {
    $uploader = new StreamingUploader($uploadDir);

    // Compute SHA-256 hash during upload via stream processor
    $checksum = null;
    $uploader->setStreamProcessor(function (\Generator $chunks, string $destPath) use (&$checksum) {
        $hash = hash_init('sha256');
        $dest = fopen($destPath, 'wb');

        foreach ($chunks as $chunk) {
            hash_update($hash, $chunk);
            fwrite($dest, $chunk);
        }

        fclose($dest);
        $checksum = hash_final($hash);
    });

    $file = $uploader->receive();

    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'ok',
        'filename' => $file->getName(),
        'size' => filesize($file->getAbsolutePath()),
        'sha256' => $checksum,
        'path' => $file->getAbsolutePath(),
    ]);
} catch (FileException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
