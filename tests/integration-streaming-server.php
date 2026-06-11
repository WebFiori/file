<?php
/**
 * Standalone server for StreamingUploader integration tests.
 */
require_once __DIR__ . '/../vendor/autoload.php';

use WebFiori\File\Exceptions\FileException;
use WebFiori\File\StreamingUploader;

header('Content-Type: application/json');

$uploadDir = __DIR__ . '/tmp/integration-uploads';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $uploader = new StreamingUploader($uploadDir);

    $maxSize = $_SERVER['HTTP_X_MAX_SIZE'] ?? null;

    if ($maxSize !== null) {
        $uploader->setMaxFileSize((int) $maxSize);
    }

    $allowedExts = $_SERVER['HTTP_X_ALLOWED_EXTS'] ?? null;

    if ($allowedExts !== null) {
        $uploader->addExts(explode(',', $allowedExts));
    }

    $file = $uploader->receive();

    echo json_encode([
        'filename' => $file->getName(),
        'size' => filesize($file->getAbsolutePath()),
        'uploaded' => $file->isUploaded(),
    ]);
} catch (FileException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
