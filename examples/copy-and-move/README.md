# Copy and Move Files

Demonstrates `copy()` and `moveTo()` methods on the File class.

## Features Shown

- `copy(destination)` — Creates a copy, returns new File instance
- `moveTo(destination)` — Moves file, updates current instance

## Run

```bash
php examples/copy-and-move/copy-and-move.php
```

## Key Points

- Both methods use streaming internally (constant memory for large files)
- `moveTo()` tries `rename()` first (instant on same filesystem)
- Parent directories are created automatically if needed
- `copy()` returns a new `FileInterface` instance for the copy
