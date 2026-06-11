<?php
/**
 * Resumable Upload Server
 *
 * Receives chunked uploads with resume support using ResumableUploader.
 * - GET  /server.php?uploadId=X&filename=Y  → returns current offset
 * - POST /server.php (X-Upload-Id, X-Filename, X-Is-Last headers) → receives chunk
 *
 * Run: php -S localhost:8080 examples/resumable-upload/router.php
 */
require_once __DIR__ . '/../../vendor/autoload.php';

use WebFiori\File\Exceptions\FileException;
use WebFiori\File\ResumableUploader;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Filename, X-Upload-Id, X-Is-Last');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$uploadDir = __DIR__ . '/../tmp/resumable-uploads';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

try {
    $uploader = new ResumableUploader($uploadDir);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $uploadId = $_GET['uploadId'] ?? '';
        $filename = $_GET['filename'] ?? '';

        if (strlen($uploadId) === 0 || strlen($filename) === 0) {
            http_response_code(400);
            echo json_encode(['error' => 'uploadId and filename are required.']);
            exit;
        }

        echo json_encode(['offset' => $uploader->getOffset($uploadId, $filename)]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $uploadId = $_SERVER['HTTP_X_UPLOAD_ID'] ?? '';
        $isLast = ($_SERVER['HTTP_X_IS_LAST'] ?? '') === 'true';

        $result = $uploader->receiveChunk($uploadId, null, $isLast);

        $response = [
            'offset' => $result['offset'],
            'complete' => $result['complete'],
        ];

        if ($result['complete'] && $result['file'] !== null) {
            $response['filename'] = $result['file']->getName();
            $response['size'] = filesize($result['file']->getAbsolutePath());
            $response['sha256'] = hash_file('sha256', $result['file']->getAbsolutePath());
        }

        echo json_encode($response);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (FileException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
