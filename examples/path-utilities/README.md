# Path Utilities

Demonstrates static utility methods for path normalization, directory management, and file existence checking.

## What It Covers

- **`File::fixPath()`** — Normalizes directory separators to the OS separator, removes trailing slashes, preserves leading slashes for absolute paths.
- **`File::isDirectory()`** — Checks if a directory exists. Pass `true` as the second argument to create it (recursively) if missing.
- **`File::isFileExist()`** — Checks if a file exists without triggering PHP warnings on invalid paths.

## Key Methods

- `File::fixPath(string $fPath)` — Returns the normalized path string.
- `File::isDirectory(string $dir, bool $createIfNot = false)` — Returns `true` if the directory exists (or was created).
- `File::isFileExist(string $path)` — Returns `true` if the file exists. Safe to call with any string.

## Run

```bash
php examples/path-utilities.php
```
