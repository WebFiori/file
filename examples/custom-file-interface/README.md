# Implementing FileInterface

Demonstrates how to create a custom `FileInterface` implementation for dependency injection, mocking, or alternative storage backends.

## What It Covers

- Creating an `InMemoryFile` class that implements `FileInterface`
- Using `FileInterface` for dependency injection in service classes
- Copying and moving in-memory files
- Mocking pattern for unit tests
- Using custom `FileInterface` implementations with uploaders via the `afterUpload` callback

## Use Cases

- **Testing** — Inject `InMemoryFile` instead of hitting the filesystem
- **Caching** — Hold file data in memory (Redis, Memcached)
- **Remote storage** — Adapter for S3, GCS, FTP
- **Database** — Store file content in BLOB columns
- **Virtual filesystem** — In-memory filesystem for testing pipelines

## Run

```bash
php examples/custom-file-interface/custom-file-interface.php
```

## Key Points

- Type-hint `FileInterface` in your service classes, not the concrete `File` class
- `FileInterface` defines: `getName`, `setName`, `getDir`, `setDir`, `getAbsolutePath`, `getExtension`, `getMIME`, `getSize`, `isExist`, `getRawData`, `setRawData`, `append`, `read`, `write`, `create`, `remove`, `copy`, `moveTo`
- `read()` and `write()` can be no-ops for in-memory implementations
- Use `MIME::getType()` to resolve MIME types from extensions in custom implementations
