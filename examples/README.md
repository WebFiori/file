# Examples

Runnable code examples for the WebFiori File library. Each example is in its own folder with a README explaining what it covers.

## Running Examples

```bash
# From the project root
php examples/read-and-write/read-and-write.php
```

Examples that create temporary files use the `examples/tmp/` directory, which is git-ignored.

## Examples List

| Example | Description |
|---------|-------------|
| [Reading and Writing Files](read-and-write/) | Create, write, read, append, and remove files |
| [File Information](file-information/) | Retrieve file metadata (name, extension, MIME, size, timestamps) |
| [Partial Read](partial-read/) | Read specific byte ranges from a file |
| [Appending Data](appending-data/) | Build up in-memory content using `append()` |
| [Base64 Encoding](base64-encoding/) | Encode/decode file data as Base64, persist encoded files |
| [Chunked Processing](chunked-processing/) | Split file data into fixed-size chunks |
| [Bytes and Hex](bytes-and-hex/) | Convert file data to byte arrays and hex strings |
| [MIME Detection](mime-detection/) | Look up MIME types by file extension |
| [Error Handling](error-handling/) | Handle `FileException` for various error conditions |
| [JSON Serialization](json-serialization/) | Convert `File` objects to JSON |
| [Path Utilities](path-utilities/) | Normalize paths, check/create directories |
| [File Upload](file-upload/) | Configure and process file uploads with `FileUploader` |
| [Serving Files](serving-files/) | Serve files over HTTP with proper headers |

## Notes

- All examples except Serving Files run in CLI mode.
- Serving Files is meant to be run with PHP's built-in server: `php -S localhost:8080 examples/serving-files/serving-files.php`
- Temporary files are cleaned up at the end of each example.
