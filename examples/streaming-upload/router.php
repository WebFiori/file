<?php
/**
 * Router for PHP built-in server.
 * 
 * Usage: php -S localhost:8080 examples/streaming-upload/router.php
 * Then open: http://localhost:8080
 */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/server.php') {
    require __DIR__.'/server.php';
    return true;
}

if ($uri === '/' || $uri === '/index.html') {
    header('Content-Type: text/html');
    readfile(__DIR__.'/index.html');
    return true;
}

return false;
