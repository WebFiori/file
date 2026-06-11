# Custom ResponseEmitter

Demonstrates how to implement the `ResponseEmitter` interface for framework integration, testing, or output transformation.

## What It Covers

- `BufferedEmitter` — Captures headers and body in memory (for testing or post-processing)
- `LoggingEmitter` — Decorator that logs all emitter operations (for debugging)
- Using custom emitters with `FileStream::serve()`

## Use Cases

- **Testing** — Assert correct headers and body without real HTTP output
- **PSR-7 integration** — Emit into a PSR-7 Response object
- **Laravel/Symfony** — Emit into framework response classes
- **Logging/monitoring** — Track what files are served and to whom
- **Transformation** — Compress, encrypt, or watermark output on the fly

## Run

```bash
php examples/custom-emitter/custom-emitter.php
```

## ResponseEmitter Interface

```php
interface ResponseEmitter {
    public function setHeader(string $name, string $value): void;
    public function setStatusCode(int $code): void;
    public function sendBody(\Generator $chunks): void;
}
```

## Key Points

- `FileStream::serve()` accepts an optional `ResponseEmitter` as second parameter
- `File::setResponseEmitter()` sets the emitter for `File::view()`
- `DefaultEmitter` uses raw `header()` + `echo` + `flush()` (the built-in default)
- The decorator pattern works well for adding logging, metrics, or caching around an existing emitter
