# Serving Files over HTTP

Demonstrates using `view()` to serve files to the browser with proper HTTP headers.

## What It Covers

- Serving a file inline (displayed in the browser) with `view(false)`
- Forcing a download dialog with `view(true)`
- Automatic HTTP header management:
  - `Content-Type` based on MIME detection
  - `Accept-Ranges: bytes` for range request support
  - `Content-Length`
  - `Content-Disposition` with the filename (inline or attachment)
  - `Content-Range` for partial content responses (HTTP 206)

## How to Run

This example must be run with a web server, not CLI:

```bash
php -S localhost:8080 examples/serving-files.php
```

Then visit:
- `http://localhost:8080` — displays the file inline
- `http://localhost:8080?download=1` — triggers a download dialog

## Key Methods

- `File::view(bool $asAttachment = false)` — Sends the file content to the client with appropriate HTTP headers. If raw data is empty, it calls `read()` first. Pass `true` to force a download instead of inline display.
