<?php

/**
 * Example: Serving Files over HTTP
 * 
 * Demonstrates serving files using view() and FileStream::serve()
 * with ResponseEmitter for framework-agnostic HTTP output.
 * 
 * Run with PHP's built-in server:
 *   php -S localhost:8080 examples/serving-files/serving-files.php
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\File;
use WebFiori\File\FileStream;
use WebFiori\File\DefaultEmitter;

// --- Method 1: File::view() (loads entire file into memory) ---
echo "=== File::view() ===\n";

$file = new File();
$file->setName('example.txt');
$file->setRawData("This is a sample file served by WebFiori File library.\nLine 2.");

// You can set a custom ResponseEmitter (default is DefaultEmitter)
// $file->setResponseEmitter(new WebFioriEmitter()); // for WebFiori framework
// $file->setResponseEmitter(new MyCustomEmitter()); // for your framework

// view(false) = inline, view(true) = attachment (download)
// Note: view() no longer calls die() by default
// $file->view(false);

echo "File ready to serve: ".$file->getName()." (".$file->getSize()." bytes)\n";

// --- Method 2: FileStream::serve() (constant memory, for large files) ---
echo "\n=== FileStream::serve() ===\n";

$samplePath = __DIR__.'/../tmp/serve-demo.txt';
file_put_contents($samplePath, "Streamed content - works for any file size.\n");

$stream = new FileStream($samplePath);

// serve() uses DefaultEmitter by default (raw header + echo + flush)
// Pass a custom emitter for framework integration:
// $stream->serve(false, new MyPsr7Emitter());

echo "Stream ready to serve: ".$stream->getName()." (".$stream->getSize()." bytes)\n";
echo "MIME: ".$stream->getMIME()."\n";

// Cleanup
unlink($samplePath);
