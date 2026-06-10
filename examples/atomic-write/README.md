# Atomic Write

Demonstrates `FileStream::writeAtomic()` for crash-safe file operations.

## Features Shown

- `writeAtomic(iterable)` — Write via temp file + rename
- Streaming from another file atomically

## Run

```bash
php examples/atomic-write/atomic-write.php
```

## Key Points

- Data is written to a temp file first (`target.tmp.PID`)
- `rename()` atomically swaps the temp into the target path
- If a crash occurs mid-write, the original file is untouched
- The temp file is cleaned up automatically on success or failure
