# Reading and Writing Files

Demonstrates core file I/O operations using the `File` class.

## What It Covers

- Creating a file with `create()`, including automatic parent directory creation via `create(true)`
- Writing content with `write(false)` (override mode) and `write(true)` (append mode)
- Reading file content back with `read()` and `getRawData()`
- Creating and writing in a single step with `write(true, true)` (the second `true` creates the file if it doesn't exist)
- Removing a file with `remove()`

## Key Methods

- `File::create(bool $createDirIfNotExist = false)` — Creates the file on disk. Pass `true` to also create missing parent directories.
- `File::setRawData(string $raw)` — Sets the in-memory content to be written.
- `File::write(bool $append = true, bool $createIfNotExist = false)` — Writes raw data to the file. First parameter controls append vs override. Second parameter creates the file if missing.
- `File::read()` — Reads the file content into memory.
- `File::getRawData()` — Returns the in-memory content as a string.
- `File::remove()` — Deletes the file from disk.

## Run

```bash
php examples/read-and-write.php
```
