# Streaming Upload (php://input)

Demonstrates `StreamingUploader` for receiving files from raw HTTP body in constant memory.

## Features Shown

- Basic streaming upload from `php://input`
- Hash verification during transfer via stream processor
- Size limit enforcement during streaming (early rejection)

## Run

```bash
php examples/streaming-upload/streaming-upload.php
```

## Key Points

- Receives raw binary body (not multipart/form-data)
- Client sends `Content-Type: application/octet-stream` with `X-Filename` header
- Constant memory regardless of file size
- Stream processor enables hash/encrypt/validate during transfer
- Constructor accepts custom input source for testing
