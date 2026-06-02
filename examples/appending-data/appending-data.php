<?php

/**
 * Example 4: Appending Data
 * 
 * Demonstrates the append() method which adds data to the in-memory buffer
 * without writing to disk. Supports both strings and arrays of strings.
 * 
 * Note: append() modifies the in-memory raw data. Call write() to persist.
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\File;

$file = new File();

// Append a single string
$file->append('Hello');
echo $file->getRawData()."\n"; // Hello

// Append another string
$file->append(' World');
echo $file->getRawData()."\n"; // Hello World

// Append multiple strings at once using an array
$file->append(['!', ' How', ' are', ' you?']);
echo $file->getRawData()."\n"; // Hello World! How are you?
