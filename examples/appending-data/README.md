# Appending Data

Demonstrates the `append()` method for building up in-memory file content.

## What It Covers

- Appending a single string to the raw data buffer
- Appending multiple strings at once by passing an array
- `append()` only modifies in-memory data — call `write()` to persist to disk

## Key Methods

- `File::append(string|array $data)` — Concatenates the given string (or each string in the array) to the existing raw data.
- `File::getRawData()` — Returns the current in-memory content.

## Run

```bash
php examples/appending-data/appending-data.php
```
