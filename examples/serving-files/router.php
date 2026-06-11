<?php
/**
 * Router for serving-files example.
 *
 * Usage: php -S localhost:8080 examples/serving-files/router.php
 *
 * Routes:
 *   /          — Index page with links
 *   /inline    — Serve file inline (File::view)
 *   /download  — Serve file as attachment (File::view with download)
 *   /stream    — Serve via FileStream::serve()
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\File;
use WebFiori\File\FileStream;

$tmpDir = __DIR__.'/../tmp';
$samplePath = $tmpDir.'/serve-demo.txt';

if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0755, true);
}

if (!file_exists($samplePath)) {
    file_put_contents($samplePath, "This file is served by the WebFiori File library.\nLine 2: Streaming works for any file size.\nLine 3: Generated at ".date('Y-m-d H:i:s')."\n");
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($uri) {
    case '/inline':
        $file = new File($samplePath);
        $file->read();
        $file->view(false);
        break;

    case '/download':
        $file = new File($samplePath);
        $file->read();
        $file->view(true);
        break;

    case '/stream':
        $stream = new FileStream($samplePath);
        $stream->serve(false);
        break;

    case '/':
        header('Content-Type: text/html');
        echo '<!DOCTYPE html><html><head><title>Serving Files</title></head><body>';
        echo '<h1>Serving Files Example</h1>';
        echo '<ul>';
        echo '<li><a href="/inline">View inline (File::view)</a></li>';
        echo '<li><a href="/download">Download (File::view as attachment)</a></li>';
        echo '<li><a href="/stream">Stream (FileStream::serve)</a></li>';
        echo '</ul>';
        echo '</body></html>';
        break;

    default:
        http_response_code(404);
        echo 'Not found';
}

return true;
