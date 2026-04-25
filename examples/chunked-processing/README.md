# Chunked Processing

Demonstrates splitting file data into fixed-size chunks for batch processing or transmission.

## What It Covers

- Splitting raw data into chunks of a given byte size
- Getting chunks as raw strings or Base64-encoded strings
- Reassembling chunks back into the original data
- Default chunk size behavior (50 bytes when a negative size is given)

## Use Cases

- Storing large files in database BLOB columns with size limits
- Transmitting files over APIs in multiple requests
- Progress tracking during file processing

## Key Methods

- `File::getChunks(int $chunkSize = 50, bool $encode = true)` — Returns an array of strings. Each string is `$chunkSize` bytes (the last chunk may be smaller). Pass `$encode = true` for Base64-encoded chunks, `false` for raw bytes.

## Run

```bash
php examples/chunked-processing.php
```
