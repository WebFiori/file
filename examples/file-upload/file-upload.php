<?php

/**
 * Example 12: File Upload Handling
 * 
 * Demonstrates using FileUploader to handle file uploads from HTML forms.
 * 
 * This example shows:
 * - Configuring allowed file extensions
 * - Processing uploads as arrays and as UploadedFile objects
 * - Checking upload status and errors
 * - Getting the max upload size from PHP config
 * 
 * In a real application, this script would be the target of an HTML form:
 * 
 *   <form method="POST" enctype="multipart/form-data" action="upload.php">
 *     <input type="file" name="user_files" multiple>
 *     <button type="submit">Upload</button>
 *   </form>
 */
require_once __DIR__.'/../../vendor/autoload.php';

// DS constant is required by FileUploader::addTestFile()
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

use WebFiori\File\Exceptions\FileException;
use WebFiori\File\FileUploader;
use WebFiori\File\UploadedFile;

// --- Configuration ---

$uploadDir = __DIR__.'/../tmp/uploads';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$uploader = new FileUploader($uploadDir);

// Add allowed extensions (rejects anything not in this list)
$uploader->addExts(['txt', 'jpg', 'png']);

// You can also add one at a time
$uploader->addExt('csv');

// Remove an extension
$uploader->removeExt('csv');

// Set the HTML form input name (default is 'files')
$uploader->setAssociatedFileName('user_files');

// Check configuration
echo "Upload dir:     ".$uploader->getUploadDir()."\n";
echo "Input name:     ".$uploader->getAssociatedFileName()."\n";
echo "Allowed types:  ".implode(', ', $uploader->getExts())."\n";
echo "Max file size:  ".FileUploader::getMaxFileSize()." KB\n";

// --- Simulated upload (for testing without a browser) ---

// Create sample files to upload
$samplePath = __DIR__.'/../tmp/sample-upload.txt';
file_put_contents($samplePath, 'Sample upload content.');

// addTestFile() populates $_FILES for CLI testing
$_SERVER['REQUEST_METHOD'] = 'POST';
FileUploader::addTestFile('user_files', $samplePath, true);

// --- Upload as UploadedFile objects ---

try {
    $files = $uploader->uploadAsFileObj();

    foreach ($files as $file) {
        if ($file instanceof UploadedFile) {
            echo "\n--- UploadedFile ---\n";
            echo "Name:      ".$file->getName()."\n";
            echo "MIME:      ".$file->getMIME()."\n";
            echo "Uploaded:  ".($file->isUploaded() ? 'yes' : 'no')."\n";
            echo "Replaced:  ".($file->isReplace() ? 'yes' : 'no')."\n";

            if (!$file->isUploaded()) {
                echo "Error:     ".$file->getUploadError()."\n";
            }

            // UploadedFile extends File, so you can read/write/serialize
            echo "JSON:      ".$file->toJSON()."\n";

            // Cleanup uploaded file
            $file->remove();
        }
    }
} catch (FileException $e) {
    echo "Upload failed: ".$e->getMessage()."\n";
}

// Cleanup
@unlink($samplePath);
@rmdir($uploadDir);
