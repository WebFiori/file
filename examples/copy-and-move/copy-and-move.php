<?php

/**
 * Example: Copy and Move Files
 * 
 * Demonstrates using copy() and moveTo() for file operations.
 * Both use streaming internally for constant memory usage.
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\File;

$tmpDir = __DIR__.'/../tmp';

// Create a source file
$source = new File($tmpDir.'/original.txt');
$source->create(true);
$source->setRawData('Original content for copy/move demo.');
$source->write(false);
echo "Created: ".$source->getAbsolutePath()."\n";

// --- Copy ---
$copy = $source->copy($tmpDir.'/copied.txt');
echo "\nCopied to: ".$copy->getAbsolutePath()."\n";
echo "Copy exists: ".($copy->isExist() ? 'yes' : 'no')."\n";
echo "Original still exists: ".($source->isExist() ? 'yes' : 'no')."\n";

// --- Move ---
$source->moveTo($tmpDir.'/moved.txt');
echo "\nMoved to: ".$source->getAbsolutePath()."\n";
echo "Old path exists: ".(file_exists($tmpDir.'/original.txt') ? 'yes' : 'no')."\n";
echo "New path exists: ".($source->isExist() ? 'yes' : 'no')."\n";

// Cleanup
$copy->remove();
$source->remove();
