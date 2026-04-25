# JSON Serialization

Demonstrates converting `File` objects to JSON for API responses or metadata storage.

## What It Covers

- Using `toJSON()` to get a `Json` object with file metadata
- Using `__toString()` (string casting) to get the JSON string directly
- The JSON output includes: `id`, `mime`, `name`, `directory`, `sizeInBytes`, `sizeInKBytes`, `sizeInMBytes`

## Key Methods

- `File::toJSON()` — Returns a `WebFiori\Json\Json` object containing file metadata.
- `File::__toString()` — Returns the JSON string representation (calls `toJSON()` internally).
- `File::setId(string $id)` — Sets a custom ID that appears in the JSON output.

## Run

```bash
php examples/json-serialization.php
```
