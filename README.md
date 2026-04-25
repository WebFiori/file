# WebFiori File

A PHP library for file operations, providing an object-oriented abstraction layer for reading, writing, uploading, and serving files with Base64 encoding/decoding, MIME type detection, and chunked file processing.

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

## Installation

```bash
composer require webfiori/file
```

## Requirements

- PHP 8.1 or higher
- `webfiori/jsonx` ^4.0

## Supported PHP Versions

|                                                                                       Build Status                                                                                        |
|:-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php81.yaml"><img src="https://github.com/WebFiori/file/actions/workflows/php81.yaml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php82.yaml"><img src="https://github.com/WebFiori/file/actions/workflows/php82.yaml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php83.yaml"><img src="https://github.com/WebFiori/file/actions/workflows/php83.yaml/badge.svg?branch=main"></a> |
| <a target="_blank" href="https://github.com/WebFiori/file/actions/workflows/php84.yaml"><img src="https://github.com/WebFiori/file/actions/workflows/php84.yaml/badge.svg?branch=main"></a> |

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

- **`File`** — Read, write, create, remove, and serve files. Supports byte-range reads, Base64 encoding/decoding, chunked processing, and JSON serialization.
- **`FileUploader`** — Handle file uploads with extension validation, size limits, and detailed error reporting.
- **`UploadedFile`** — Extends `File` with upload-specific properties (upload status, replacement status, error message).
- **`MIME`** — Static lookup of ~600 file extension to MIME type mappings.

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
| [Serving Files](examples/serving-files/) | Serve files over HTTP with proper headers |

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
