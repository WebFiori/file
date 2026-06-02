<?php

/**
 * Example 9: Error Handling
 * 
 * Demonstrates how the library throws FileException for various error
 * conditions: missing files, empty names/paths, invalid Base64, and
 * end-of-file overruns.
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\Exceptions\FileException;
use WebFiori\File\File;

// 1. Reading a file that doesn't exist
try {
    $file = new File('/tmp/does-not-exist.txt');
    $file->read();
} catch (FileException $e) {
    echo "Error 1: ".$e->getMessage()."\n";
    // "File not found: '/tmp/does-not-exist.txt'."
}

// 2. Reading with no file name set
try {
    $file = new File();
    $file->read();
} catch (FileException $e) {
    echo "Error 2: ".$e->getMessage()."\n";
    // "File name cannot be empty string."
}

// 3. Reading with name but no path (and file not in calling directory)
try {
    $file = new File('nonexistent.txt');
    $file->read();
} catch (FileException $e) {
    echo "Error 3: ".$e->getMessage()."\n";
    // "Path cannot be empty string."
}

// 4. Writing with no data set
try {
    $file = new File(__DIR__.'/../tmp/empty-write.txt');
    $file->create(true);
    $file->write(false); // No data set via setRawData()
} catch (FileException $e) {
    echo "Error 4: ".$e->getMessage()."\n";
    // "No data is set to write."
    // Cleanup
    (new File(__DIR__.'/../tmp/empty-write.txt'))->remove();
}

// 5. Reading past end of file
try {
    $file = new File(__DIR__.'/../tmp/small.txt');
    $file->create(true);
    $file->setRawData('Short');
    $file->write(false);
    $file->read(0, 100); // File is only 5 bytes
} catch (FileException $e) {
    echo "Error 5: ".$e->getMessage()."\n";
    // "Reached end of file while trying to read 100 byte(s)."
    (new File(__DIR__.'/../tmp/small.txt'))->remove();
}

// 6. Strict Base64 decoding with invalid characters
try {
    $file = new File();
    $file->setRawData('not@valid!base64', true, true);
} catch (FileException $e) {
    echo "Error 6: ".$e->getMessage()."\n";
    // "Base 64 decoding failed due to characters outside base 64 alphabet."
}
