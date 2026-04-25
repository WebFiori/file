# Partial Read (Byte Ranges)

Demonstrates reading specific byte ranges from a file instead of loading the entire content.

## What It Covers

- Reading a subset of bytes with `read($from, $to)`
- `$from` is the starting byte position (inclusive)
- `$to` is the ending byte position (exclusive)
- Calling `read()` with no arguments reads the entire file
- Throws `FileException` if the requested range exceeds the file size

## Use Cases

- Resumable downloads
- Reading headers from large binary files
- Sampling content without loading everything into memory

## Key Methods

- `File::read(int $from = -1, int $to = -1)` — When both default to `-1`, reads the full file. Otherwise reads bytes from `$from` up to `$to`.

## Run

```bash
php examples/partial-read.php
```
