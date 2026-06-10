# Streaming File I/O

Demonstrates `FileStream` for constant-memory file operations.

## Features Shown

- `readChunks()` — Read file in fixed-size chunks
- `readLines()` — Read file line by line
- `readRange()` — Read specific byte ranges
- `File::stream()` — Bridge from File to FileStream

## Run

```bash
php examples/streaming/streaming.php
```

## Key Points

- Memory usage stays constant regardless of file size
- Generators yield one chunk at a time — use `foreach`
- `readChunks()` buffer size is configurable
- `File::stream()` creates a FileStream from any File instance
