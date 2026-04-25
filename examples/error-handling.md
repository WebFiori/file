# Error Handling

Demonstrates the `FileException` scenarios thrown by the library.

## What It Covers

Six error conditions, each caught with `try/catch`:

1. **File not found** — `read()` on a path that doesn't exist
2. **Empty file name** — `read()` with no name set
3. **Empty path** — `read()` with a name but no directory (and file not in calling script's directory)
4. **No data to write** — `write()` without calling `setRawData()` first
5. **Read past end of file** — `read($from, $to)` where `$to` exceeds the file size
6. **Invalid Base64** — `setRawData($data, true, true)` with strict mode on invalid input

## Key Class

- `WebFiori\File\Exceptions\FileException` — Extends PHP's `Exception`. Thrown by `File` and `FileUploader` methods on error.

## Run

```bash
php examples/error-handling.php
```
