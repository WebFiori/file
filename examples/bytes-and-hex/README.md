# Binary Representation (Bytes and Hex Arrays)

Demonstrates converting file data to byte arrays and hexadecimal string arrays.

## What It Covers

- Converting raw data to an array of integers (0–255) with `toBytesArray()`
- Converting raw data to an array of two-character hex strings with `toHexArray()`
- Round-tripping hex back to the original string with `hex2bin()`
- Unicode content support

## Use Cases

- Binary file analysis and inspection
- Generating hex dumps
- Computing checksums or comparing binary content

## Key Methods

- `File::toBytesArray()` — Returns `array<int>` where each element is a byte value (0–255).
- `File::toHexArray()` — Returns `array<string>` where each element is a two-character uppercase hex string (e.g. `'4F'`).

## Run

```bash
php examples/bytes-and-hex/bytes-and-hex.php
```
