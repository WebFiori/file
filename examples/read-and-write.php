<?php
/**
 * Example 1: Reading and Writing Files
 * 
 * Demonstrates how to create a file, write content to it, read it back,
 * append additional content, and remove the file.
 */
require_once __DIR__ . '/../vendor/autoload.php';

use WebFiori\File\File;

// Create a new file (pass true to also create parent directories if needed)
$file = new File(__DIR__ . '/tmp/hello.txt');
$file->create(true);

// Write content (false = override mode)
$file->setRawData('Hello, World!');
$file->write(false);

// Read the file back
$file->read();
echo $file->getRawData() . "\n"; // Hello, World!

// Append content (true = append mode, which is also the default)
$file->setRawData(' Welcome.');
$file->write(true);

// Read again to see the full content
$file->read();
echo $file->getRawData() . "\n"; // Hello, World! Welcome.

// You can also write and create in one step if the file doesn't exist yet
$file2 = new File(__DIR__ . '/tmp/auto-created.txt');
$file2->setRawData('Created automatically.');
$file2->write(true, true); // append=true, createIfNotExist=true

$file2->read();
echo $file2->getRawData() . "\n"; // Created automatically.

// Cleanup
$file->remove();
$file2->remove();
