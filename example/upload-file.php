<?php
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '../vendor/autoload.php';


use webfiori\file\FileUploader;
use webfiori\file\UploadedFile;

$u = new FileUploader(__DIR__.DIRECTORY_SEPARATOR.'uploads');
$u->addExts([
    'txt', 'doc', 'docx', 'png', 'jpg'
]);
$files = $u->uploadAsFileObj();
$file = $files[0];
if ($file instanceof UploadedFile) {
    if (!$file->isUploaded()) {
        http_response_code(404);
        echo 'File not uploaded due to error code: '.$file->getUploadError();
    } else {
        echo 'Successfully uploaded.';
    }
}