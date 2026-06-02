<?php

/**
 * Example 10: JSON Serialization
 * 
 * Demonstrates converting File objects to JSON. Useful for API responses
 * or storing file metadata. The File class implements JsonI interface.
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\File;

// Create a file with some data
$file = new File(__DIR__.'/../tmp/report.txt');
$file->create(true);
$file->setRawData('Quarterly report content.');
$file->write(false);
$file->setId('rpt-2026-q1');

// toJSON() returns a Json object with file metadata
$json = $file->toJSON();
echo $json."\n";
// Output (formatted):
// {
//   "id": "rpt-2026-q1",
//   "mime": "text/plain",
//   "name": "report.txt",
//   "directory": "/path/to/examples/tmp",
//   "sizeInBytes": 25,
//   "sizeInKBytes": 0.0244140625,
//   "sizeInMBytes": 2.384185791015625e-5
// }

// __toString() also returns the JSON string
echo "\nAs string: ".$file."\n";

// Cleanup
$file->remove();
