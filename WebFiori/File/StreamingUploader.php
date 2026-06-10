<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026 WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\File;

use WebFiori\File\Exceptions\FileException;

/**
 * An uploader that receives a single file from php://input in constant memory.
 *
 * Unlike FileUploader which works with $_FILES (multipart form uploads),
 * this class reads raw binary body data directly from the input stream,
 * enabling large file uploads without temp file overhead.
 *
 * @author Ibrahim
 */
class StreamingUploader extends AbstractUploader {
    /**
     * @var string
     */
    private string $inputSource;

    /**
     * Creates a new StreamingUploader instance.
     *
     * @param string $uploadDir Directory to store uploaded files.
     * @param array $allowedTypes Allowed file extensions.
     * @param string $inputSource Input stream path (override for testing).
     */
    public function __construct(string $uploadDir = '', array $allowedTypes = [], string $inputSource = 'php://input') {
        parent::__construct($uploadDir, $allowedTypes);
        $this->inputSource = $inputSource;
    }

    /**
     * Receives a file from the input stream and saves it to the upload directory.
     *
     * @param string|null $filename The filename to use. If null, attempts to
     *        read from X-Filename header or Content-Disposition header.
     *
     * @return UploadedFile The uploaded file instance.
     *
     * @throws FileException If validation fails or the file cannot be written.
     */
    public function receive(?string $filename = null): UploadedFile {
        if (strlen($this->getUploadDir()) === 0) {
            throw new FileException('Upload path is not set.');
        }

        $filename = $this->resolveFilename($filename);
        $filename = self::sanitizeFilename($filename);

        if (strlen($filename) === 0) {
            throw new FileException('Filename is empty after sanitization.');
        }

        if (count($this->getExts()) > 0 && !$this->isValidExt($filename)) {
            throw new FileException('File type not allowed.');
        }

        // Early size check from Content-Length header
        $maxSize = $this->getMaxFileSizeLimit();

        if ($maxSize !== null && isset($_SERVER['CONTENT_LENGTH'])) {
            if ((int) $_SERVER['CONTENT_LENGTH'] > $maxSize) {
                throw new FileException('File exceeds size limit.');
            }
        }

        $fileInfo = [
            'name' => $filename,
            'upload-path' => $this->getUploadDir(),
        ];

        if (!$this->fireBeforeUpload($fileInfo)) {
            throw new FileException('Upload rejected by callback.');
        }

        $destPath = $this->getUploadDir() . DIRECTORY_SEPARATOR . $filename;
        $chunks = $this->readInput();
        $processor = $this->getStreamProcessor();

        if ($processor !== null) {
            $processor($chunks, $destPath);
        } else {
            file_put_contents($destPath, '');
            $stream = new FileStream($destPath);
            $stream->writeFromStream($chunks, false);
        }

        // Verify size after write
        if ($maxSize !== null && File::isFileExist($destPath) && filesize($destPath) > $maxSize) {
            unlink($destPath);
            throw new FileException('File exceeds size limit.');
        }

        $file = new UploadedFile($filename, $this->getUploadDir());
        $file->setIsUploaded(true);
        $this->fireAfterUpload($file);

        return $file;
    }

    /**
     * Reads from the input source as a generator.
     *
     * @param int $bufferSize Buffer size in bytes.
     *
     * @return \Generator Yields string chunks.
     *
     * @throws FileException If unable to open input.
     */
    private function readInput(int $bufferSize = 8192): \Generator {
        $input = @fopen($this->inputSource, 'rb');

        if (!is_resource($input)) {
            throw new FileException('Unable to open input stream.');
        }

        $maxSize = $this->getMaxFileSizeLimit();

        try {
            $bytesRead = 0;

            while (!feof($input)) {
                $chunk = fread($input, $bufferSize);

                if ($chunk === false || strlen($chunk) === 0) {
                    break;
                }

                $bytesRead += strlen($chunk);

                if ($maxSize !== null && $bytesRead > $maxSize) {
                    throw new FileException('File exceeds size limit.');
                }

                yield $chunk;
            }
        } finally {
            fclose($input);
        }
    }

    /**
     * Resolves the filename from parameter, headers, or default.
     *
     * @param string|null $filename Explicit filename if provided.
     *
     * @return string Resolved filename.
     */
    private function resolveFilename(?string $filename): string {
        if ($filename !== null && strlen($filename) > 0) {
            return $filename;
        }

        if (isset($_SERVER['HTTP_X_FILENAME'])) {
            return $_SERVER['HTTP_X_FILENAME'];
        }

        if (isset($_SERVER['HTTP_CONTENT_DISPOSITION'])) {
            if (preg_match('/filename="?([^";\s]+)"?/', $_SERVER['HTTP_CONTENT_DISPOSITION'], $matches)) {
                return $matches[1];
            }
        }

        return 'upload.bin';
    }
}
