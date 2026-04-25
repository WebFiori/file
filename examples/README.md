# Examples

Runnable code examples for the WebFiori File library. Each example is a standalone PHP script that can be executed directly.

## Running Examples

```bash
# From the project root
php examples/read-and-write.php
```

Examples that create temporary files use the `examples/tmp/` directory, which is git-ignored.

## Examples List

| Example | Description |
|---------|-------------|
| [read-and-write](read-and-write.php) | Create, write, read, append, and remove files |
| [file-information](file-information.php) | Retrieve file metadata (name, extension, MIME, size, timestamps) |
| [partial-read](partial-read.php) | Read specific byte ranges from a file |
| [appending-data](appending-data.php) | Build up in-memory content using `append()` |
| [base64-encoding](base64-encoding.php) | Encode/decode file data as Base64, persist encoded files |
| [chunked-processing](chunked-processing.php) | Split file data into fixed-size chunks |
| [bytes-and-hex](bytes-and-hex.php) | Convert file data to byte arrays and hex strings |
| [mime-detection](mime-detection.php) | Look up MIME types by file extension |
| [error-handling](error-handling.php) | Handle `FileException` for various error conditions |
| [json-serialization](json-serialization.php) | Convert `File` objects to JSON |
| [path-utilities](path-utilities.php) | Normalize paths, check/create directories |
| [file-upload](file-upload.php) | Configure and process file uploads with `FileUploader` |
| [serving-files](serving-files.php) | Serve files over HTTP with proper headers |

## Notes

- All examples except `serving-files.php` run in CLI mode.
- `serving-files.php` is meant to be run with PHP's built-in server: `php -S localhost:8080 examples/serving-files.php`
- Temporary files are cleaned up at the end of each example.
