<?php
/**
 * Example 13: Serving Files over HTTP
 * 
 * Demonstrates using view() to serve files to the browser with proper
 * HTTP headers (Content-Type, Content-Disposition, Content-Range).
 * 
 * Run this with PHP's built-in server:
 *   php -S localhost:8080 examples/serving-files.php
 * 
 * Then visit:
 *   http://localhost:8080              (inline display)
 *   http://localhost:8080?download=1   (force download)
 */
require_once __DIR__ . '/../vendor/autoload.php';

use WebFiori\File\File;

// Create a sample file to serve
$file = new File();
$file->setName('example.txt');
$file->setRawData("This is a sample file served by WebFiori File library.\nLine 2 of content.");

// view(false) = display inline in browser (Content-Disposition: inline)
// view(true)  = force download dialog  (Content-Disposition: attachment)
$forceDownload = isset($_GET['download']);
$file->view($forceDownload);

// The view() method automatically:
// 1. Sets Content-Type based on MIME detection (text/plain for .txt)
// 2. Sets Accept-Ranges: bytes (enables range requests for streaming)
// 3. Sets Content-Length
// 4. Sets Content-Disposition with the filename
// 5. Handles HTTP_RANGE header for partial content (206 responses)
// 6. Outputs the file data
