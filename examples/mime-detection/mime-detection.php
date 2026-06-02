<?php

/**
 * Example 8: MIME Type Detection
 * 
 * Demonstrates using the MIME class to look up MIME types by file extension.
 * The library includes ~600 extension-to-MIME mappings.
 * Unknown extensions return 'application/octet-stream'.
 */
require_once __DIR__.'/../../vendor/autoload.php';

use WebFiori\File\MIME;

// Common file types
echo "jpg:  ".MIME::getType('jpg')."\n";  // image/jpeg
echo "png:  ".MIME::getType('png')."\n";  // image/png
echo "gif:  ".MIME::getType('gif')."\n";  // image/gif
echo "txt:  ".MIME::getType('txt')."\n";  // text/plain
echo "html: ".MIME::getType('html')."\n"; // text/html
echo "css:  ".MIME::getType('css')."\n";  // text/css
echo "js:   ".MIME::getType('js')."\n";   // text/javascript
echo "mp3:  ".MIME::getType('mp3')."\n";  // audio/mpeg
echo "mp4:  ".MIME::getType('mp4')."\n";  // video/mp4
echo "zip:  ".MIME::getType('zip')."\n";  // application/zip
echo "docx: ".MIME::getType('docx')."\n"; // application/vnd.openxmlformats-officedocument.wordprocessingml.document
echo "xlsx: ".MIME::getType('xlsx')."\n"; // application/vnd.openxmlformats-officedocument.spreadsheetml.sheet

// Case-insensitive and handles leading dots
echo "\nJPG:  ".MIME::getType('JPG')."\n";  // image/jpeg
echo ".png: ".MIME::getType('.png')."\n";   // image/png

// Unknown extensions return the default MIME type
echo "\nxyz:  ".MIME::getType('xyz')."\n"; // chemical/x-xyz (this one is actually mapped!)
echo "zzz:  ".MIME::getType('zzz')."\n";  // application/octet-stream

// MIME type is also auto-detected when setting a file name
use WebFiori\File\File;

$file = new File();
$file->setName('photo.jpg');
echo "\nAuto-detected MIME: ".$file->getMIME()."\n"; // image/jpeg
