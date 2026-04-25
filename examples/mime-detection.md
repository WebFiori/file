# MIME Type Detection

Demonstrates looking up MIME types by file extension using the `MIME` class.

## What It Covers

- Looking up MIME types for common extensions (images, documents, audio, video, archives)
- Case-insensitive lookups (`JPG` works the same as `jpg`)
- Leading dot handling (`.png` is treated as `png`)
- Default return value for unknown extensions (`application/octet-stream`)
- Automatic MIME detection when setting a file name via `File::setName()`

## Key Methods

- `MIME::getType(string $ext)` — Returns the MIME type string for the given extension. Returns `'application/octet-stream'` if the extension is not recognized. The library includes ~600 extension-to-MIME mappings.

## Run

```bash
php examples/mime-detection.php
```
