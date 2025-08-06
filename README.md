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
    * [File Information](#file-information)
  * [File Upload](#file-upload)
    * [Basic Upload](#basic-upload)
    * [Upload as File Objects](#upload-as-file-objects)
    * [Upload Configuration](#upload-configuration)
  * [Base64 Encoding/Decoding](#base64-encodingdecoding)
  * [File Serving](#file-serving)
  * [Chunked Processing](#chunked-processing)
  * [MIME Type Detection](#mime-type-detection)
* [Working Example](#working-example)
* [Testing](#testing)
* [API Reference](#api-reference)
  * [File Class](#file-class)
  * [FileUploader Class](#fileuploader-class)
  * [UploadedFile Class](#uploadedfile-class)
  * [MIME Class](#mime-class)
* [Error Handling](#error-handling)
* [Security Considerations](#security-considerations)
* [Performance Tips](#performance-tips)
* [Contributing](#contributing)
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

- **Object-Oriented File Operations**: Clean, intuitive API for file manipulation with comprehensive error handling
- **Advanced File Upload**: Multi-file upload with type validation, size limits, and detailed error reporting
- **Base64 Encoding/Decoding**: Built-in support for binary data encoding with strict validation options
- **MIME Type Detection**: Automatic MIME type detection for common file extensions
- **File Serving**: HTTP-compliant file serving with range request support for streaming
- **Chunked Processing**: Memory-efficient processing of large files with configurable chunk sizes
- **JSON Serialization**: Convert file objects to JSON for APIs and data interchange
- **Path Normalization**: Cross-platform path handling with automatic directory separator conversion
- **Security Features**: File type validation, directory traversal protection, and safe file operations
- **Comprehensive Testing**: Full test coverage with PHPUnit for reliability and stability

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
    echo "File size: " . $file->getSize() . " bytes\n";
    echo "MIME type: " . $file->getMIME() . "\n";
    echo "Last modified: " . $file->getLastModified() . "\n";
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
$file->setRawData("\nAppended content");
$file->write(true); // true = append mode

// Override file content
$file->setRawData('New content');
$file->write(false); // false = override mode
```

#### File Information

```php
<?php
use WebFiori\File\File;

$file = new File('document.pdf', '/uploads');

// Get file properties
echo "Name: " . $file->getName() . "\n";
echo "Extension: " . $file->getExtension() . "\n";
echo "Directory: " . $file->getDir() . "\n";
echo "Full path: " . $file->getAbsolutePath() . "\n";
echo "MIME type: " . $file->getMIME() . "\n";
echo "Size: " . $file->getSize() . " bytes\n";

// Get last modified time
echo "Modified: " . $file->getLastModified('Y-m-d H:i:s') . "\n";

// Convert to different formats
$bytesArray = $file->toBytesArray();
$hexArray = $file->toHexArray();
$jsonData = $file->toJSON();
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
            echo "File: " . $file->getName() . "\n";
            echo "Size: " . $file->getSize() . " bytes\n";
            echo "MIME: " . $file->getMIME() . "\n";
            echo "Replaced existing: " . ($file->isReplace() ? 'Yes' : 'No') . "\n";
            
            // Process the uploaded file
            $file->read();
            $content = $file->getRawData();
        } else {
            echo "Upload failed: " . $file->getUploadError() . "\n";
        }
    }
}
```

#### Upload Configuration

```php
<?php
use WebFiori\File\FileUploader;

$uploader = new FileUploader('/uploads');

// Configure allowed file types
$uploader->addExts(['jpg', 'png', 'gif', 'pdf', 'docx']);

// Remove specific extensions
$uploader->removeExt('gif');

// Set form input name
$uploader->setAssociatedFileName('user_files');

// Get current configuration
$allowedTypes = $uploader->getExts();
$uploadDir = $uploader->getUploadDir();
$inputName = $uploader->getAssociatedFileName();

// Get maximum upload size (from PHP configuration)
$maxSize = FileUploader::getMaxFileSize(); // Returns size in KB
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

### MIME Type Detection

```php
<?php
use WebFiori\File\MIME;

// Get MIME type by extension
$mimeType = MIME::getType('jpg'); // Returns: image/jpeg
$pdfMime = MIME::getType('pdf');  // Returns: application/pdf
$txtMime = MIME::getType('txt');  // Returns: text/plain

// Unknown extensions return default MIME type
$unknownMime = MIME::getType('xyz'); // Returns: application/octet-stream

// The library supports common file extensions including:
// - Images: jpg, png, gif, bmp, svg, webp, tiff
// - Documents: pdf, doc, docx, xls, xlsx, ppt, pptx
// - Audio: mp3, wav, ogg, flac, aac
// - Video: mp4, avi, mov, wmv, flv
// - Archives: zip, rar, 7z, tar, gz
// - And many more...
```

## Working Example

The library includes a complete working example demonstrating file upload functionality with a modern web interface. The example consists of:

- **Frontend**: Vue.js application with Vuetify UI components
- **Backend**: PHP script using WebFiori File library
- **Features**: Drag-and-drop upload, progress feedback, error handling

### Running the Example

```bash
# Navigate to the example directory
cd example

# Start PHP development server
php -S localhost:8000

# Open browser and visit
# http://localhost:8000/page.html
```

The example demonstrates:
- File type validation (txt, doc, docx, png, jpg)
- Real-time upload progress
- Error handling and user feedback
- Secure file storage

For detailed setup instructions, see [example/readme.md](example/readme.md).

## Testing

The library includes comprehensive test coverage using PHPUnit:

```bash
# Run all tests
composer test

# Run tests with PHPUnit 10
composer test-10

# Run specific test class
./vendor/bin/phpunit tests/WebFiori/Framework/Test/File/FileTest.php
```

### Test Coverage

The test suite covers:
- **File Operations**: Reading, writing, creating, deleting files
- **Upload Functionality**: Single and multi-file uploads, validation
- **Base64 Encoding**: Encoding/decoding with error handling
- **MIME Detection**: Extension-based MIME type detection
- **Error Scenarios**: Invalid paths, permissions, file not found
- **Edge Cases**: Empty files, large files, special characters

The library includes comprehensive test coverage with extensive test cases.

### UploadedFile Class

#### Constructor
```php
public function __construct(string $fName = '', string $fPath = '')
```

#### Core Methods

| Method | Description | Parameters | Return Type |
|--------|-------------|------------|-------------|
| `isUploaded()` | Check if file was uploaded successfully | - | `bool` |
| `isReplace()` | Check if file replaced existing file | - | `bool` |
| `getUploadError()` | Get upload error message | - | `string` |
| `setIsUploaded($bool)` | Set upload status | `bool $bool` | `void` |
| `setIsReplace($bool)` | Set replacement status | `bool $bool` | `void` |
| `setUploadErr($err)` | Set upload error message | `string $err` | `void` |

### MIME Class

#### Static Methods

| Method | Description | Parameters | Return Type |
|--------|-------------|------------|-------------|
| `getType($ext)` | Get MIME type by extension | `string $ext` | `string` |

The MIME class contains a comprehensive mapping of file extensions to their corresponding MIME types.

## Error Handling

The library uses the `FileException` class for error handling:

```php
<?php
use WebFiori\File\File;
use WebFiori\File\Exceptions\FileException;

try {
    $file = new File('/path/to/nonexistent.txt');
    $file->read();
} catch (FileException $e) {
    echo "Error: " . $e->getMessage();
    // Handle specific error scenarios
    switch ($e->getCode()) {
        case 0:
            // File not found
            break;
        default:
            // Other errors
            break;
    }
}
```

Common exceptions thrown:
- **File not found**: When attempting to read non-existent files
- **Permission denied**: When lacking read/write permissions
- **Invalid path**: When providing malformed file paths
- **Base64 decode error**: When decoding invalid Base64 data
- **Upload errors**: Various upload-related failures

## Security Considerations

### File Upload Security

```php
<?php
use WebFiori\File\FileUploader;

$uploader = new FileUploader('/secure/uploads');

// 1. Restrict file types
$uploader->addExts(['jpg', 'png', 'pdf']); // Only allow safe file types

// 2. Validate file size (handled by PHP settings)
// Set in php.ini: upload_max_filesize = 5M

// 3. Use secure upload directory outside web root
// Store files outside public_html or www directories

// 4. Sanitize file names (handled automatically)
// The library normalizes file paths and names
```

### Path Traversal Protection

The library automatically:
- Normalizes directory separators across platforms
- Prevents directory traversal attacks
- Validates file paths before operations
- Uses secure file handling functions

### Best Practices

1. **Always validate file types** before processing
2. **Store uploads outside the web root** when possible
3. **Set appropriate file permissions** (644 for files, 755 for directories)
4. **Implement file size limits** via PHP configuration
5. **Scan uploaded files** for malware when possible
6. **Use HTTPS** for file upload forms

## Performance Tips

### Memory Management

```php
<?php
use WebFiori\File\File;

// For large files, use chunked processing
$file = new File('/path/to/large-video.mp4');
$file->read();

// Process in 1MB chunks to avoid memory issues
$chunks = $file->getChunks(1024 * 1024, false);
foreach ($chunks as $chunk) {
    // Process each chunk separately
    processChunk($chunk);
}
```

### Optimization Tips

1. **Use appropriate chunk sizes** for large files (1MB recommended)
2. **Read only necessary byte ranges** when possible
3. **Cache MIME type detection** results for repeated operations
4. **Use streaming** for file serving when available
5. **Implement proper error handling** to avoid resource leaks

### File Serving Optimization

```php
<?php
use WebFiori\File\File;

$file = new File('/path/to/document.pdf');
$file->read();

// Enable range requests for better streaming
$file->view(false); // Serves with proper HTTP headers
```

## Contributing

We welcome contributions! Please follow these guidelines:

1. **Fork the repository** and create a feature branch
2. **Write tests** for new functionality
3. **Follow PSR-12** coding standards
4. **Update documentation** for new features
5. **Submit a pull request** with clear description

### Development Setup

```bash
# Clone the repository
git clone https://github.com/WebFiori/file.git
cd file

# Install dependencies
composer install

# Run tests
composer test

# Run code style checks
./vendor/bin/php-cs-fixer fix --dry-run
```

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/

# Run specific test file
./vendor/bin/phpunit tests/WebFiori/Framework/Test/File/FileTest.php
```


## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

