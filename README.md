# WebFiori File

A comprehensive PHP library for file operations, providing an object-oriented abstraction layer for reading, writing, uploading, and serving files with advanced features like Base64 encoding/decoding, MIME type detection, and chunked file processing.

<p align="center">
  <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php84.yaml">
    <img src="https://github.com/WebFiori/file/actions/workflows/php84.yaml/badge.svg?branch=main">
  </a>
  <a href="https://codecov.io/gh/WebFiori/file">
    <img src="https://codecov.io/gh/WebFiori/file/branch/main/graph/badge.svg" />
  </a>
  <a href="https://sonarcloud.io/dashboard?id=WebFiori_file">
      <img src="https://sonarcloud.io/api/project_badges/measure?project=WebFiori_file&metric=alert_status" />
  </a>
  <a href="https://packagist.org/packages/webfiori/file">
    <img src="https://img.shields.io/packagist/dt/webfiori/file?color=light-green">
  </a>
</p>

## Table of Contents

* [Installation](#installation)
* [Requirements](#requirements)
* [Supported PHP Versions](#supported-php-versions)
* [Key Features](#key-features)
* [Quick Start](#quick-start)
* [Core Classes](#core-classes)
* [Usage Examples](#usage-examples)
  * [Basic File Operations](#basic-file-operations)
    * [Reading Files](#reading-files)
    * [Writing Files](#writing-files)
  * [File Upload](#file-upload)
    * [Basic Upload](#basic-upload)
    * [Upload as File Objects](#upload-as-file-objects)
  * [Base64 Encoding/Decoding](#base64-encodingdecoding)
  * [File Serving](#file-serving)
  * [Chunked Processing](#chunked-processing)
* [API Reference](#api-reference)
  * [File Class](#file-class)
  * [FileUploader Class](#fileuploader-class)
* [License](#license)

## Installation

Install via Composer:

```bash
composer require webfiori/file
```

## Requirements

- **PHP**: 8.0 or higher
- **Dependencies**: 
  - `webfiori/jsonx`: ^4.0 (JSON serialization)
  - `webfiori/http`: * (HTTP response handling - dev dependency)

## Supported PHP Versions

|                                                                                       Build Status                                                                                        |
|:-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php80.yaml"><img src="https://github.com/WebFiori/file/actions/workflows/php80.yaml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php81.yaml"><img src="https://github.com/WebFiori/file/actions/workflows/php81.yaml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php82.yaml"><img src="https://github.com/WebFiori/file/actions/workflows/php82.yaml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php83.yaml"><img src="https://github.com/WebFiori/file/actions/workflows/php83.yaml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php84.yaml"><img src="https://github.com/WebFiori/file/actions/workflows/php84.yaml/badge.svg?branch=main"></a> |

## Key Features

- **Object-Oriented File Operations**: Clean, intuitive API for file manipulation
- **Advanced File Upload**: Multi-file upload with type validation and error handling
- **Base64 Encoding/Decoding**: Built-in support for binary data encoding
- **MIME Type Detection**: Automatic MIME type detection for file extensions
- **File Serving**: HTTP-compliant file serving with range request support
- **Chunked Processing**: Memory-efficient processing of large files
- **JSON Serialization**: Convert file objects to JSON for APIs

## Quick Start

```php
<?php
require_once 'vendor/autoload.php';

use WebFiori\File\File;

// Basic file operations
$file = new File('path/to/document.txt');
$file->read();
echo $file->getRawData();
```

```php
<?php
require_once 'vendor/autoload.php';

use WebFiori\File\FileUploader;

// File upload
$uploader = new FileUploader('/uploads');
$uploader->addExts(['jpg', 'png', 'pdf']);
$uploadedFiles = $uploader->uploadAsFileObj();
```

## Core Classes

### File
The main class for file operations, providing methods for reading, writing, and manipulating files.

### FileUploader
Handles file uploads with validation, type checking, and error management.

### UploadedFile
Extends File class to represent uploaded files with additional upload-specific properties.

### MIME
Utility class for MIME type detection based on file extensions.

## Usage Examples

### Basic File Operations

#### Reading Files

```php
<?php
use WebFiori\File\File;

// Read entire file
$file = new File('/path/to/file.txt');
$file->read();
$content = $file->getRawData();

// Read specific byte range
$file->read(10, 100); // Read bytes 10-100
$partialContent = $file->getRawData();

// Check if file exists
if ($file->isExist()) {
    echo "File size: " . $file->getSize() . " bytes";
    echo "MIME type: " . $file->getMIME();
    echo "Last modified: " . $file->getLastModified();
}
```

#### Writing Files

```php
<?php
use WebFiori\File\File;

// Create new file
$file = new File('/path/to/new-file.txt');
$file->create();
$file->setRawData('Hello, World!');
$file->write();

// Append to existing file
$file->setRawData('\\nAppended content');
$file->write(true); // true = append mode

// Override file content
$file->setRawData('New content');
$file->write(false); // false = override mode
```

### File Upload

#### Basic Upload

```php
<?php
use WebFiori\File\FileUploader;
use WebFiori\File\Exceptions\FileException;

try {
    $uploader = new FileUploader('/var/www/uploads');
    
    // Set allowed file types
    $uploader->addExts(['jpg', 'png', 'gif', 'pdf', 'docx']);
    
    // Set the HTML form input name
    $uploader->setAssociatedFileName('user_files');
    
    // Upload files
    $results = $uploader->upload();
    
    foreach ($results as $result) {
        if ($result['uploaded']) {
            echo "✅ {$result['name']} uploaded successfully\\n";
        } else {
            echo "❌ {$result['name']} failed: {$result['upload-error']}\\n";
        }
    }
} catch (FileException $e) {
    echo "Upload error: " . $e->getMessage();
}
```

#### Upload as File Objects

```php
<?php
use WebFiori\File\FileUploader;
use WebFiori\File\UploadedFile;

$uploader = new FileUploader('/uploads');
$uploader->addExts(['jpg', 'png']);

$uploadedFiles = $uploader->uploadAsFileObj();

foreach ($uploadedFiles as $file) {
    if ($file instanceof UploadedFile) {
        if ($file->isUploaded()) {
            echo "File: " . $file->getName();
            echo "Size: " . $file->getSize() . " bytes";
            echo "MIME: " . $file->getMIME();
            
            // Process the uploaded file
            $file->read();
            $content = $file->getRawData();
        } else {
            echo "Upload failed: " . $file->getUploadError();
        }
    }
}
```

### Base64 Encoding/Decoding

```php
<?php
use WebFiori\File\File;

$file = new File('image.jpg');
$file->read();

// Get Base64 encoded content
$encoded = $file->getRawData(true);
echo "Base64: " . $encoded;

// Decode Base64 data
$file2 = new File();
$file2->setRawData($encoded, true); // true = decode from Base64
$decoded = $file2->getRawData();

// Write encoded file
$file->writeEncoded(); // Creates 'image.jpg.bin' with Base64 content

// Read and decode encoded file
$encodedFile = new File('image.jpg.bin');
$encodedFile->readDecoded();
$originalData = $encodedFile->getRawData();
```

### File Serving

```php
<?php
use WebFiori\File\File;

// Serve file for download
$file = new File('/path/to/document.pdf');
$file->read();
$file->view(true); // true = force download

// Serve file inline (display in browser)
$file->view(false); // false = display inline

// The view() method automatically:
// - Sets appropriate Content-Type header
// - Handles Content-Range for partial requests
// - Sets Content-Disposition header
// - Outputs file content
```

### Chunked Processing

```php
<?php
use WebFiori\File\File;

// Process large file in chunks
$file = new File('/path/to/large-file.mp4');
$file->read();

// Get 1KB chunks, Base64 encoded
$chunks = $file->getChunks(1024, true);

foreach ($chunks as $index => $chunk) {
    echo "Processing chunk " . ($index + 1) . "/" . count($chunks) . "\\n";
    
    // Store chunk in database or process separately
    // Each chunk is Base64 encoded and 1KB in size
    storeChunkInDatabase($index, $chunk);
}

// Get raw chunks (not encoded)
$rawChunks = $file->getChunks(1024, false);
```

## API Reference

### File Class

#### Constructor
```php
public function __construct(string $fNameOrAbsPath = '', string $fPath = '')
```

#### Core Methods

| Method | Description | Parameters | Return Type |
|--------|-------------|------------|-------------|
| `read($from, $to)` | Read file content | `int $from = -1`, `int $to = -1` | `void` |
| `write($append, $createIfNotExist)` | Write data to file | `bool $append = true`, `bool $createIfNotExist = false` | `void` |
| `create($createDirIfNotExist)` | Create new file | `bool $createDirIfNotExist = false` | `void` |
| `remove()` | Delete file | - | `bool` |
| `isExist()` | Check if file exists | - | `bool` |
| `getSize()` | Get file size in bytes | - | `int` |
| `getMIME()` | Get MIME type | - | `string` |
| `getName()` | Get file name | - | `string` |
| `getExtension()` | Get file extension | - | `string` |
| `getDir()` | Get directory path | - | `string` |
| `getAbsolutePath()` | Get full file path | - | `string` |
| `getRawData($encode)` | Get file content | `bool $encode = false` | `string` |
| `setRawData($data, $decode, $strict)` | Set file content | `string $data`, `bool $decode = false`, `bool $strict = false` | `void` |
| `getChunks($chunkSize, $encode)` | Split content into chunks | `int $chunkSize = 50`, `bool $encode = true` | `array` |
| `view($asAttachment)` | Serve file via HTTP | `bool $asAttachment = false` | `void` |
| `toJSON()` | Convert to JSON | - | `Json` |

### FileUploader Class

#### Constructor
```php
public function __construct(string $uploadPath = '', array $allowedTypes = [])
```

#### Core Methods

| Method | Description | Parameters | Return Type |
|--------|-------------|------------|-------------|
| `addExt($ext)` | Add allowed extension | `string $ext` | `bool` |
| `addExts($arr)` | Add multiple extensions | `array $arr` | `array` |
| `removeExt($ext)` | Remove allowed extension | `string $ext` | `bool` |
| `setUploadDir($dir)` | Set upload directory | `string $dir` | `void` |
| `setAssociatedFileName($name)` | Set form input name | `string $name` | `void` |
| `upload($replaceIfExist)` | Upload files (array result) | `bool $replaceIfExist = false` | `array` |
| `uploadAsFileObj($replaceIfExist)` | Upload files (object result) | `bool $replaceIfExist = false` | `array` |
| `getExts()` | Get allowed extensions | - | `array` |
| `getUploadDir()` | Get upload directory | - | `string` |


## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

