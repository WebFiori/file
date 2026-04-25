<?php
/**
 * Example 11: Path Utilities
 * 
 * Demonstrates static utility methods for path normalization, directory
 * checking/creation, and file existence checking.
 */
require_once __DIR__ . '/../vendor/autoload.php';

use WebFiori\File\File;

$ds = DIRECTORY_SEPARATOR;

// --- fixPath(): normalizes slashes to the OS directory separator ---

echo "fixPath examples:\n";
echo "  'home/user/docs'   => " . File::fixPath('home/user/docs') . "\n";
// Linux: home/user/docs   Windows: home\user\docs

echo "  'C:\\Users\\docs\\'  => " . File::fixPath('C:\\Users\\docs\\') . "\n";
// Trailing slashes removed, separators normalized

echo "  '/var/www/'         => " . File::fixPath('/var/www/') . "\n";
// Leading slash preserved: /var/www

// --- isDirectory(): check if a directory exists, optionally create it ---

echo "\nisDirectory examples:\n";
$tmpDir = __DIR__ . '/tmp';

echo "  Exists (tmp):     " . (File::isDirectory($tmpDir) ? 'yes' : 'no') . "\n";
echo "  Exists (fake):    " . (File::isDirectory($tmpDir . '/nope') ? 'yes' : 'no') . "\n";

// Create a directory if it doesn't exist
$newDir = $tmpDir . '/auto-created';
File::isDirectory($newDir, true); // true = create if missing
echo "  Created new dir:  " . (is_dir($newDir) ? 'yes' : 'no') . "\n";
@rmdir($newDir);

// --- isFileExist(): check file existence without triggering PHP errors ---

echo "\nisFileExist examples:\n";
$file = new File($tmpDir . '/probe.txt');
$file->create(true);
echo "  Exists:           " . (File::isFileExist($file->getAbsolutePath()) ? 'yes' : 'no') . "\n";
$file->remove();
echo "  After remove:     " . (File::isFileExist($file->getAbsolutePath()) ? 'yes' : 'no') . "\n";

// Safe with invalid paths — no PHP warning
echo "  Invalid path:     " . (File::isFileExist("") ? 'yes' : 'no') . "\n";
