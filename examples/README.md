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
| [Streaming I/O](streaming/) | Read chunks, lines, ranges, and write with `FileStream` |
| [Copy and Move](copy-and-move/) | Copy and move files with streaming |
| [Atomic Write](atomic-write/) | Crash-safe writes with temp + rename |
| [Streaming Upload](streaming-upload/) | One-shot streaming upload from `php://input` |
| [Upload Callbacks](upload-callbacks/) | Before/after hooks for validation and logging |
| [Resumable Upload](resumable-upload/) | Chunked upload with pause/resume support |
| [Custom FileInterface](custom-file-interface/) | Implement `FileInterface` for DI, mocking, or custom storage |
| [Custom Emitter](custom-emitter/) | Implement `ResponseEmitter` for framework integration |

## Web Examples

Some examples include a `router.php` for use with PHP's built-in server:

```bash
php -S localhost:8080 examples/serving-files/router.php
php -S localhost:8080 examples/streaming-upload/router.php
php -S localhost:8080 examples/resumable-upload/router.php
```

## Notes

- All examples run in CLI mode for quick testing.
- Web examples also have a router for browser-based demos.
- Temporary files are cleaned up at the end of each example.
