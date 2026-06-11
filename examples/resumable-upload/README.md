# Resumable Upload

Demonstrates `ResumableUploader` for chunked file uploads with pause/resume support.

## Features Shown

- Multi-chunk upload across separate requests
- Resume from server-reported byte offset after failure
- Pause and resume from the client
- `beforeUpload` / `afterUpload` callbacks
- Stale partial file cleanup

## Run (CLI demo)

```bash
php examples/resumable-upload/resumable-upload.php
```

## Run (Web UI)

```bash
php -S localhost:8080 examples/resumable-upload/router.php
```

Then open http://localhost:8080, drop a file, and use the Pause/Resume buttons.

## Protocol

```
GET  /server.php?uploadId=abc&filename=file.bin  → {"offset": 524288}
POST /server.php (X-Upload-Id, X-Filename, X-Is-Last headers, raw body)  → {"offset": N, "complete": bool}
```

## Key Points

- Partial files stored in `{uploadDir}/.partial/{uploadId}_{filename}`
- File size on disk = authoritative byte offset (no database needed)
- Client asks server for offset before resuming
- Final chunk triggers move from `.partial/` to upload directory
- Stream processor runs on finalization for hash/transform of the complete file
