<?php

/**
 * Example: Upload Callback Hooks
 * 
 * Demonstrates using onBeforeUpload and onAfterUpload callbacks
 * for validation, logging, and post-processing.
 */
require_once __DIR__.'/../../vendor/autoload.php';

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

use WebFiori\File\FileUploader;
use WebFiori\File\UploadedFile;

$tmpDir = __DIR__.'/../tmp';
$uploadDir = $tmpDir.'/hook-uploads';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Create a sample file
$samplePath = $tmpDir.'/hook-test.txt';
file_put_contents($samplePath, 'Content for hook demo.');

// --- Before-upload hook (validation) ---
echo "=== Before-Upload Hook ===\n";

$uploader = new FileUploader($uploadDir, ['txt']);
$_SERVER['REQUEST_METHOD'] = 'POST';

$uploader->setOnBeforeUpload(function(array $fileInfo)
{
    echo "  Checking: ".$fileInfo['name']."\n";

    // Reject files with "blocked" in the name
    if (str_contains($fileInfo['name'], 'blocked')) {
        echo "  REJECTED\n";

        return false;
    }
    echo "  ACCEPTED\n";

    return true;
});

FileUploader::addTestFile('files', $samplePath, true);
$result = $uploader->upload();
echo "  Uploaded: ".($result[0]['uploaded'] ? 'yes' : 'no')."\n";

// Cleanup first upload
if ($result[0]['uploaded']) {
    unlink($uploadDir.DS.$result[0]['name']);
}

// --- After-upload hook (logging/processing) ---
echo "\n=== After-Upload Hook ===\n";

$uploader2 = new FileUploader($uploadDir, ['txt']);

$uploader2->setOnAfterUpload(function(UploadedFile $file)
{
    echo "  Logged upload: ".$file->getName()." at ".$file->getDir()."\n";
    echo "  MIME: ".$file->getMIME()."\n";
});

FileUploader::addTestFile('files', $samplePath, true);
$result2 = $uploader2->upload();
echo "  Uploaded: ".($result2[0]['uploaded'] ? 'yes' : 'no')."\n";

// Cleanup
if ($result2[0]['uploaded']) {
    unlink($uploadDir.DS.$result2[0]['name']);
}
unlink($samplePath);
@rmdir($uploadDir);
