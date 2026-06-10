# Upload Callback Hooks

Demonstrates `setOnBeforeUpload()` and `setOnAfterUpload()` for validation and logging.

## Features Shown

- `setOnBeforeUpload(callback)` — Validate/reject before file is moved
- `setOnAfterUpload(callback)` — Log/process after successful upload

## Run

```bash
php examples/upload-callbacks/upload-callbacks.php
```

## Key Points

- Before-upload callback receives the file info array
- Return `false` from before-upload to reject the file
- After-upload callback receives the `UploadedFile` object
- Both hooks work with `FileUploader` and `StreamingUploader`
