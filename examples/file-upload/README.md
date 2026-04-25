# File Upload Handling

Demonstrates configuring and processing file uploads using `FileUploader` and `UploadedFile`.

## What It Covers

- Setting the upload directory and allowed file extensions
- Adding/removing extensions with `addExt()`, `addExts()`, `removeExt()`
- Setting the HTML form input name with `setAssociatedFileName()`
- Checking the PHP max upload size with `FileUploader::getMaxFileSize()`
- Simulating uploads in CLI with `FileUploader::addTestFile()` (useful for testing)
- Processing uploads as `UploadedFile` objects with `uploadAsFileObj()`
- Inspecting upload results: `isUploaded()`, `isReplace()`, `getUploadError()`

## HTML Form Integration

In a real application, the uploader processes files from an HTML form:

```html
<form method="POST" enctype="multipart/form-data" action="upload.php">
    <input type="file" name="user_files" multiple>
    <button type="submit">Upload</button>
</form>
```

The `name` attribute of the file input must match the value set via `setAssociatedFileName()`.

## Key Classes

- `FileUploader` — Configures and executes file uploads with extension validation.
- `UploadedFile` — Extends `File` with upload-specific properties (`isUploaded()`, `isReplace()`, `getUploadError()`).

## Run

```bash
php examples/file-upload.php
```

> **Note:** This example uses `addTestFile()` to simulate an upload in CLI mode. The `DS` constant (directory separator) must be defined, which the example handles automatically.
