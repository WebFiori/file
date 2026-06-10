<?php
/**
 * Streaming Upload Server
 * 
 * Receives chunked uploads from the frontend with pause/resume support.
 * Each chunk is appended to the destination file as it arrives.
 * 
 * Run: php -S localhost:8080 examples/streaming-upload/server.php
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\Exceptions\FileException;
use WebFiori\File\FileStream;
use WebFiori\File\AbstractUploader;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Filename, X-Chunk-Index, X-Total-Chunks, X-Upload-Id');
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

$filename = $_SERVER['HTTP_X_FILENAME'] ?? 'upload.bin';
$filename = AbstractUploader::sanitizeFilename($filename);
$chunkIndex = (int)($_SERVER['HTTP_X_CHUNK_INDEX'] ?? 0);
$totalChunks = (int)($_SERVER['HTTP_X_TOTAL_CHUNKS'] ?? 1);
$uploadId = $_SERVER['HTTP_X_UPLOAD_ID'] ?? uniqid();

$destPath = $uploadDir . DIRECTORY_SEPARATOR . $uploadId . '_' . $filename;

try {
    // Read chunk from php://input and append to file
    $input = fopen('php://input', 'rb');

    if (!is_resource($input)) {
        throw new FileException('Unable to read input.');
    }

    $dest = fopen($destPath, 'ab'); // append mode
    $bytesWritten = 0;

    while (!feof($input)) {
        $chunk = fread($input, 8192);

        if ($chunk === false || strlen($chunk) === 0) {
            break;
        }

        fwrite($dest, $chunk);
        $bytesWritten += strlen($chunk);
    }

    fclose($input);
    fclose($dest);

    $complete = ($chunkIndex + 1) >= $totalChunks;

    $response = [
        'status' => 'ok',
        'chunk' => $chunkIndex,
        'totalChunks' => $totalChunks,
        'bytesWritten' => $bytesWritten,
        'complete' => $complete,
        'filename' => $filename,
    ];

    if ($complete) {
        $finalSize = filesize($destPath);
        $response['finalSize'] = $finalSize;
        $response['sha256'] = hash_file('sha256', $destPath);

        // Rename to final name (remove upload ID prefix)
        $finalPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($finalPath)) {
            unlink($finalPath);
        }
        rename($destPath, $finalPath);
        $response['path'] = $finalPath;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
