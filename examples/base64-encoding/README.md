# Base64 Encoding and Decoding

Demonstrates encoding file data to Base64 and decoding it back, both in-memory and file-based.

## What It Covers

- Getting Base64-encoded content with `getRawData(true)` or `getRawDataEncoded()`
- Decoding Base64 data with `setRawData($data, true)`
- Strict mode decoding that throws `FileException` on invalid characters: `setRawData($data, true, true)`
- Persisting encoded data to a `.bin` file with `writeEncoded()`
- Reading and decoding a `.bin` file with `readDecoded()`

## Use Cases

- Transmitting binary files over JSON APIs
- Embedding file data in email or database text fields
- Storing encoded backups

## Key Methods

- `File::getRawData(bool $encode = false)` — Pass `true` to get Base64-encoded output.
- `File::getRawDataEncoded()` — Shorthand for `getRawData(true)`.
- `File::setRawData(string $raw, bool $decode = false, bool $strict = false)` — Pass `$decode = true` to decode from Base64. Pass `$strict = true` to reject data with characters outside the Base64 alphabet.
- `File::writeEncoded()` — Writes Base64-encoded content to `{filename}.bin`.
- `File::readDecoded()` — Reads a file and decodes its content from Base64.

## Run

```bash
php examples/base64-encoding.php
```
