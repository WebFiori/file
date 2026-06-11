# Serving Files over HTTP

Demonstrates serving files using `File::view()` and `FileStream::serve()` with the `ResponseEmitter` interface.

## What It Covers

- Serving a file inline in the browser with `File::view(false)`
- Serving a file as a download (attachment) with `File::view(true)`
- Streaming large files with constant memory via `FileStream::serve()`
- The `ResponseEmitter` interface for framework-agnostic HTTP output
- `DefaultEmitter` (raw `header()` + `echo`) vs custom emitters

## Run (CLI demo)

```bash
php examples/serving-files/serving-files.php
```

## Run (Web UI)

```bash
php -S localhost:8080 examples/serving-files/router.php
```

Then visit:
- http://localhost:8080/inline — View file inline
- http://localhost:8080/download — Download as attachment
- http://localhost:8080/stream — Serve via FileStream

## Key Methods

- `File::view(bool $asAttachment = false)` — Sends HTTP headers and outputs the file content. Loads the full file into memory.
- `FileStream::serve(bool $asAttachment = false, ?ResponseEmitter $emitter = null)` — Streams the file in chunks with constant memory. Ideal for large files.
- `File::setResponseEmitter(ResponseEmitter $emitter)` — Plug in a custom emitter for PSR-7 or framework integration.

## ResponseEmitter Interface

```php
interface ResponseEmitter {
    public function setStatusCode(int $code): void;
    public function setHeader(string $name, string $value): void;
    public function sendBody(iterable $chunks): void;
}
```

Implement this interface to integrate file serving with any framework (Laravel, Symfony, PSR-7, etc.).
