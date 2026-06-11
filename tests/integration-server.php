<?php
/**
 * Standalone server script for ResumableUploader integration tests.
 * Started by the test as a background process.
 */
require_once __DIR__ . '/../vendor/autoload.php';

use WebFiori\File\Exceptions\FileException;
use WebFiori\File\ResumableUploader;

header('Content-Type: application/json');

$uploadDir = __DIR__ . '/tmp/integration-uploads';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

try {
    $uploader = new ResumableUploader($uploadDir);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $uploadId = $_GET['uploadId'] ?? '';
        $filename = $_GET['filename'] ?? '';

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
