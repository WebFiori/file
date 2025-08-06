# WebFiori File Upload Example

This example demonstrates how to use the WebFiori File library to create a complete file upload system with a modern web interface. The example consists of a Vue.js frontend with Vuetify UI components and a PHP backend that handles file uploads.

## Overview

The example includes:
- **Frontend**: A responsive web interface built with Vue.js 2 and Vuetify 2
- **Backend**: PHP script using WebFiori File library for secure file uploads
- **File Storage**: Local directory for uploaded files with configurable restrictions

## Files Structure

```
example/
├── page.html          # Frontend HTML page with Vue.js application
├── upload-file.php    # Backend PHP script for handling uploads
├── uploads/           # Directory where uploaded files are stored
└── readme.md          # This documentation file
```

## Prerequisites

- **PHP 8.0 or higher**
- **Composer** (for dependency management)
- **Web server** (Apache, Nginx, or PHP built-in server)

## Installation & Setup

### 1. Install Dependencies

First, navigate to the project root directory and install the required dependencies:

```bash
# Navigate to the project root (parent directory of 'example')
cd /path/to/webfiori-file

# Install dependencies using Composer
composer install
```

### 2. Set Up File Permissions

Ensure the uploads directory is writable by the web server:

```bash
# Make uploads directory writable
chmod 755 example/uploads
```

### 3. Start PHP Development Server

You can run the example using PHP's built-in development server:

```bash
# Navigate to the example directory
cd example

# Start PHP development server on port 8000
php -S localhost:8000

# Or specify a different port
php -S localhost:3000
```

### 4. Access the Application

Open your web browser and navigate to:
- `http://localhost:8000/page.html` (or your chosen port)

## How It Works

### Frontend (page.html)

The frontend is a single-page application built with:

- **Vue.js 2**: JavaScript framework for reactive UI
- **Vuetify 2**: Material Design component library
- **AJAXRequest.js**: Lightweight AJAX library for file uploads

**Key Features:**
- File selection with drag-and-drop support
- Real-time upload progress feedback
- Success/error notifications via snackbar
- Responsive design that works on mobile and desktop

**Frontend Flow:**
1. User selects a file using the file input component
2. Vue.js detects the file selection change
3. AJAX request is sent to `upload-file.php` with the selected file
4. User receives immediate feedback about upload status

### Backend (upload-file.php)

The backend script handles file uploads using the WebFiori File library:

```php
<?php
// Enable error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

// Load WebFiori File library
require_once '../vendor/autoload.php';

use WebFiori\File\FileUploader;
use WebFiori\File\UploadedFile;

// Create FileUploader instance pointing to uploads directory
$u = new FileUploader(__DIR__.DIRECTORY_SEPARATOR.'uploads');

// Configure allowed file extensions
$u->addExts([
    'txt', 'doc', 'docx', 'png', 'jpg'
]);

// Process uploaded files and return File objects
$files = $u->uploadAsFileObj();
$file = $files[0];

// Check upload status and respond accordingly
if ($file instanceof UploadedFile) {
    if (!$file->isUploaded()) {
        http_response_code(404);
        echo 'File not uploaded due to error code: '.$file->getUploadError();
    } else {
        echo 'Successfully uploaded.';
    }
}
```

**Backend Features:**
- **File Type Validation**: Only allows specific file extensions (txt, doc, docx, png, jpg)
- **Error Handling**: Provides detailed error messages for failed uploads
- **Secure Storage**: Files are stored in a designated uploads directory
- **Object-Oriented**: Uses WebFiori's UploadedFile objects for better file management

## Configuration Options

### Allowed File Types

You can modify the allowed file extensions in `upload-file.php`:

```php
$u->addExts([
    'txt', 'doc', 'docx', 'pdf',  // Documents
    'png', 'jpg', 'jpeg', 'gif',  // Images
    'mp4', 'avi', 'mov',          // Videos
    'zip', 'rar'                  // Archives
]);
```

### Upload Directory

Change the upload destination by modifying the FileUploader constructor:

```php
// Upload to a different directory
$u = new FileUploader('/path/to/your/upload/directory');
```

### File Size Limits

PHP file upload limits are controlled by php.ini settings:

```ini
; Maximum file size for uploads
upload_max_filesize = 10M

; Maximum POST data size
post_max_size = 10M

; Maximum execution time
max_execution_time = 300
```

## Security Considerations

The example includes several security measures:

1. **File Type Validation**: Only specific file extensions are allowed
2. **Directory Isolation**: Uploaded files are stored in a separate directory
3. **Error Handling**: Prevents information disclosure through proper error handling
4. **Server-Side Validation**: All validation happens on the server side

## Troubleshooting

### Common Issues

1. **"File not uploaded" error**:
   - Check file permissions on the uploads directory
   - Verify the file type is in the allowed extensions list
   - Check PHP upload limits in php.ini

2. **AJAX errors**:
   - Ensure PHP development server is running
   - Check browser console for JavaScript errors
   - Verify the upload-file.php path is correct

3. **Composer dependencies missing**:
   - Run `composer install` in the project root directory
   - Ensure PHP version is 8.0 or higher

### Debug Mode

The example includes debug settings in upload-file.php. For production use, disable these:

```php
// Remove or comment out these lines for production
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
```

## Extending the Example

You can extend this example by:

- Adding file size validation
- Implementing user authentication
- Adding file preview functionality
- Creating file management features (delete, rename, etc.)
- Adding progress bars for large file uploads
- Implementing multiple file upload support

## API Reference

For detailed information about the WebFiori File library classes and methods used in this example, refer to the main project documentation.

## License

This example is part of the WebFiori File library and is licensed under the MIT License.
