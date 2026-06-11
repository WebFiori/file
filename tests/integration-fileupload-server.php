<?php
/**
 * Standalone server for FileUploader integration tests.
 * Handles multipart/form-data file uploads via $_FILES.
 */
require_once __DIR__ . '/../vendor/autoload.php';

use WebFiori\File\Exceptions\FileException;
use WebFiori\File\FileUploader;

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
    $uploader = new FileUploader();
    $uploader->setUploadDir($uploadDir);
    $uploader->setAssociatedFileName('file');

    $allowedExts = $_POST['allowed_exts'] ?? null;

    if ($allowedExts !== null) {
        $uploader->addExts(explode(',', $allowedExts));
    } else {
        // FileUploader requires at least one allowed extension
        $uploader->addExts(['txt', 'bin', 'dat', 'pdf', 'png', 'jpg']);
    }

    $files = $uploader->uploadAsFileObj();

    $result = [];

    foreach ($files as $file) {
        $result[] = [
            'name' => $file->getName(),
            'uploaded' => $file->isUploaded(),
            'error' => $file->getUploadError(),
            'replaced' => $file->isReplace(),
        ];
    }

    echo json_encode(['files' => $result]);
} catch (FileException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
