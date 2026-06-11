# WebFiori File

A PHP library for file operations, providing an object-oriented abstraction layer for reading, writing, uploading, and serving files with streaming support, Base64 encoding/decoding, MIME type detection, and chunked file processing.

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
  <a href="https://github.com/WebFiori/file/releases">
    <img src="https://img.shields.io/github/v/release/WebFiori/file">
  </a>
  <a href="https://packagist.org/packages/webfiori/file">
    <img src="https://img.shields.io/packagist/dt/webfiori/file?color=light-green">
  </a>
</p>

## Table of Contents

- [Supported PHP Versions](#supported-php-versions)
- [Key Features](#key-features)
- [Why This Library?](#why-this-library)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Core Classes](#core-classes)
- [Using FileInterface](#using-fileinterface)
- [Examples](#examples)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)
- [Support](#support)
- [Changelog](#changelog)

## Supported PHP Versions

|                                                                                       Build Status                                                                                        |
|:-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php81.yaml"><img src="https://github.com/WebFiori/file/actions/workflows/php81.yaml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php82.yaml"><img src="https://github.com/WebFiori/file/actions/workflows/php82.yaml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php83.yaml"><img src="https://github.com/WebFiori/file/actions/workflows/php83.yaml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php84.yaml"><img src="https://github.com/WebFiori/file/actions/workflows/php84.yaml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php85.yaml"><img src="https://github.com/WebFiori/file/actions/workflows/php85.yaml/badge.svg?branch=main"></a> |

## Key Features

- Object-oriented file read, write, create, remove, copy, and move
- Streaming I/O with constant memory usage via `FileStream`
- File upload handling with extension validation, size limits, and callback hooks
- Stream processing during uploads with hash verification support
- Atomic writes (temp + rename) for crash-safe operations
- Base64 encoding/decoding
- Byte-range reads for partial file access
- MIME type detection for ~600 file extensions
- `ResponseEmitter` interface for framework-agnostic HTTP file serving
- `FileInterface` for dependency injection and mocking

## Why This Library?

- **All-in-one** — File I/O, uploads, streaming, and HTTP serving in a single package.
- **Upload-first design** — `FileUploader`, `StreamingUploader`, and `ResumableUploader` handle multipart forms, raw body streams, and chunked uploads with pause/resume — each with extension filtering, size limits, and callback hooks.
- **Constant-memory streaming** — Generators power reads, writes, uploads, and serving. Process gigabyte files without touching memory limits.
- **Framework-agnostic HTTP serving** — The `ResponseEmitter` interface decouples file serving from any specific HTTP layer. Plug in PSR-7, any framework, or raw PHP.
- **Testable by design** — `FileInterface` enables dependency injection and mocking. No filesystem required in your unit tests.
- **Lightweight** — Single dependency, no framework coupling.

## Installation

```bash
composer require webfiori/file
```

**Requirements:**
- PHP 8.1 or higher
- `webfiori/jsonx` ^4.0

## Quick Start

```php
<?php
require_once 'vendor/autoload.php';

use WebFiori\File\File;

$file = new File('/path/to/document.txt');
$file->read();
echo $file->getRawData();
```

## Core Classes

- **`FileInterface`** — Contract defining core file operations. Use for type-hinting and mocking.
- **`File`** — Read, write, create, remove, copy, move, and serve files. Supports byte-range reads, Base64 encoding/decoding, chunked processing, and JSON serialization.
- **`FileStream`** — Streaming file I/O with constant memory usage. Read chunks, lines, ranges, and serve large files.
- **`FileUploader`** — Handle multipart form file uploads with extension validation, size limits, stream processing, and callback hooks.
- **`StreamingUploader`** — Receive files from raw HTTP body (`php://input`) in constant memory. Ideal for single-shot binary uploads.
- **`ResumableUploader`** — Chunked upload handler with resume-on-failure support. Tracks byte offset on disk for seamless resume after network drops.
- **`UploadedFile`** — Extends `File` with upload-specific properties (upload status, replacement status, error message).
- **`ResponseEmitter`** — Interface for abstracting HTTP output when serving files.
- **`MIME`** — Static lookup of ~600 file extension to MIME type mappings.

## Using FileInterface

Type-hint `FileInterface` when your code only needs I/O operations. Use the concrete `File` class when you need encoding, serialization, or HTTP features.

```php
use WebFiori\File\FileInterface;

class DocumentService {
    public function process(FileInterface $file): string {
        $file->read();
        return strtoupper($file->getRawData());
    }
}
```

### Mocking in Tests

```php
use WebFiori\File\FileInterface;

$mockFile = $this->createMock(FileInterface::class);
$mockFile->method('getRawData')->willReturn('test content');
$mockFile->method('getName')->willReturn('test.txt');
$mockFile->method('isExist')->willReturn(true);

$service = new DocumentService();
$result = $service->process($mockFile);
```

## Examples

The [examples/](examples/) directory contains runnable PHP scripts covering every feature of the library:

| Example | Description |
|---------|-------------|
| [Reading and Writing Files](examples/read-and-write/) | Create, write, read, append, and remove files |
| [File Information](examples/file-information/) | File metadata: name, extension, MIME, size, timestamps, constructor variants |
| [Partial Read](examples/partial-read/) | Read specific byte ranges from a file |
| [Appending Data](examples/appending-data/) | Build up in-memory content with `append()` |
| [Base64 Encoding](examples/base64-encoding/) | Encode/decode Base64, `writeEncoded()`, `readDecoded()` |
| [Chunked Processing](examples/chunked-processing/) | Split file data into fixed-size chunks |
| [Bytes and Hex](examples/bytes-and-hex/) | Convert data to byte arrays and hex strings |
| [MIME Detection](examples/mime-detection/) | Look up MIME types by extension |
| [Error Handling](examples/error-handling/) | `FileException` scenarios and how to handle them |
| [JSON Serialization](examples/json-serialization/) | Convert `File` objects to JSON |
| [Path Utilities](examples/path-utilities/) | Path normalization, directory creation, file existence checks |
| [File Upload](examples/file-upload/) | Configure and process uploads with `FileUploader` |
| [Serving Files](examples/serving-files/) | Serve files over HTTP with ResponseEmitter |
| [Streaming I/O](examples/streaming/) | Read chunks, lines, and ranges with `FileStream` |
| [Copy and Move](examples/copy-and-move/) | Copy and move files with streaming |
| [Atomic Write](examples/atomic-write/) | Crash-safe writes with temp + rename |
| [Streaming Upload](examples/streaming-upload/) | Receive large files from `php://input` |
| [Upload Callbacks](examples/upload-callbacks/) | Before/after hooks for validation and logging |
| [Resumable Upload](examples/resumable-upload/) | Chunked upload with pause/resume support |
| [Custom FileInterface](examples/custom-file-interface/) | Implement `FileInterface` for DI, mocking, or custom storage |
| [Custom Emitter](examples/custom-emitter/) | Implement `ResponseEmitter` for framework integration |

Each example has its own README with detailed explanations. Run any example with:

```bash
php examples/read-and-write/read-and-write.php
```

## Testing

```bash
# Run all tests
composer test

# Run tests with PHPUnit 10
composer test10
```

## Contributing

1. Fork the repository and create a feature branch
2. Write tests for new functionality
3. Follow PSR-12 coding standards
4. Submit a pull request

## License

MIT — see [LICENSE](LICENSE) for details.

## Support

Found a bug or have a feature request? [Open an issue](https://github.com/WebFiori/file/issues).

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history.
