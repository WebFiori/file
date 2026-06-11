# Streaming Upload (php://input)

Demonstrates `StreamingUploader` for receiving files from raw HTTP body in constant memory.

## Features Shown

- One-shot streaming upload from `php://input`
- Filename resolved from `X-Filename` header
- SHA-256 hash verification during transfer via stream processor
- Size limit enforcement during streaming

## Run (CLI demo)

```bash
php examples/streaming-upload/streaming-upload.php
```

## Run (Web UI)

```bash
php -S localhost:8080 examples/streaming-upload/router.php
```

Then open http://localhost:8080 and drop a file.

## Key Points

- Receives raw binary body (not multipart/form-data)
- Client sends `Content-Type: application/octet-stream` with `X-Filename` header
- Constant memory regardless of file size
- Stream processor enables hash/encrypt/validate during transfer
- Constructor accepts custom input source for testing
