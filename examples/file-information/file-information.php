<?php

/**
 * Example 2: File Information and Properties
 * 
 * Demonstrates how to retrieve file metadata: name, extension, directory,
 * MIME type, size, last modified time, and ID assignment for database use.
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\File;

// Create a sample file to inspect
$file = new File(__DIR__.'/../tmp/document.txt');
$file->create(true);
$file->setRawData('Sample content for inspection.');
$file->write(false);

// Basic properties
echo "Name:           ".$file->getName()."\n";           // document.txt
echo "Name (no ext):  ".$file->getNameWithNoExt()."\n";  // document
echo "Extension:      ".$file->getExtension()."\n";      // txt
echo "Directory:      ".$file->getDir()."\n";             // /path/to/examples/tmp
echo "Absolute path:  ".$file->getAbsolutePath()."\n";   // /path/to/examples/tmp/document.txt
echo "MIME type:      ".$file->getMIME()."\n";            // text/plain
echo "Size (bytes):   ".$file->getSize()."\n";            // 29
echo "Exists:         ".($file->isExist() ? 'yes' : 'no')."\n"; // yes

// Last modified time
echo "Modified:       ".$file->getLastModified('Y-m-d H:i:s')."\n"; // e.g. 2026-04-25 02:50:00
echo "Modified (ts):  ".$file->getLastModified(null)."\n";          // Unix timestamp

// Assign an ID (useful when storing file references in a database)
$file->setId('record-42');
echo "ID:             ".$file->getID()."\n"; // record-42

// Constructor variants:
// 1) Absolute path as single argument
$f1 = new File('/home/user/docs/report.txt');
echo "\nFrom absolute path -> Name: ".$f1->getName().", Dir: ".$f1->getDir()."\n";

// 2) Name + directory as separate arguments
$f2 = new File('report.txt', '/home/user/docs');
echo "From name+dir    -> Name: ".$f2->getName().", Dir: ".$f2->getDir()."\n";

// Cleanup
$file->remove();
