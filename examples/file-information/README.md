# File Information and Properties

Demonstrates how to retrieve file metadata and shows the two constructor forms.

## What It Covers

- Getting file name, name without extension, and extension
- Getting directory path and absolute path
- MIME type detection (automatic from file extension)
- File size in bytes
- Last modified time as formatted string or Unix timestamp
- Assigning a custom ID for database use
- Constructor with absolute path: `new File('/path/to/file.txt')`
- Constructor with name + directory: `new File('file.txt', '/path/to')`

## Key Methods

- `File::getName()` / `File::getNameWithNoExt()` / `File::getExtension()`
- `File::getDir()` / `File::getAbsolutePath()`
- `File::getMIME()` — Returns MIME type based on file extension.
- `File::getSize()` — Returns size in bytes. Returns `null` if file doesn't exist and no data is set.
- `File::getLastModified(?string $format)` — Pass a date format string, or `null` for a raw Unix timestamp. Returns `0` if the file doesn't exist.
- `File::setId(string $id)` / `File::getID()` — Custom identifier for database-backed file storage.
- `File::isExist()` — Checks if the file exists on disk.

## Run

```bash
php examples/file-information.php
```
