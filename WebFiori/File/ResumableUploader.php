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
 * A chunked uploader that supports resume-on-failure.
 *
 * Each chunk is a separate request. The partial file's size on disk serves as
 * the authoritative byte offset — no database or session storage is needed.
 *
 * Partial files are stored in a `.partial/` subdirectory inside the upload
 * directory and moved to the final location on completion.
 *
 * @author Ibrahim
 */
class ResumableUploader extends AbstractUploader {
    /**
     * @var string
     */
    private string $inputSource;

    /**
     * @var string|null
     */
    private ?string $partialDir = null;

    /**
     * Creates a new ResumableUploader instance.
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
     * Cancels an in-progress upload by removing the partial file.
     *
     * @param string $uploadId Unique upload session identifier.
     * @param string $filename The filename.
     */
    public function cancel(string $uploadId, string $filename): void {
        $filename = self::sanitizeFilename($filename);
        $path = $this->getPartialPath($uploadId, $filename);

        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Removes partial files older than the given age.
     *
     * @param int $maxAgeSeconds Maximum age in seconds.
     *
     * @return int Number of partial files removed.
     */
    public function cleanStale(int $maxAgeSeconds): int {
        $partialDir = $this->getPartialDir();

        if (!is_dir($partialDir)) {
            return 0;
        }

        $count = 0;
        $now = time();
        $files = glob($partialDir.DIRECTORY_SEPARATOR.'*');

        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > $maxAgeSeconds) {
                unlink($file);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Returns the current byte offset for a given upload session.
     *
     * @param string $uploadId Unique upload session identifier.
     * @param string $filename The filename.
     *
     * @return int Current byte offset. Returns 0 if no partial file exists.
     */
    public function getOffset(string $uploadId, string $filename): int {
        $filename = self::sanitizeFilename($filename);
        $path = $this->getPartialPath($uploadId, $filename);

        if (file_exists($path)) {
            return filesize($path);
        }

        return 0;
    }

    /**
     * Returns the directory used for storing partial uploads.
     *
     * @return string The partial directory path.
     */
    public function getPartialDir(): string {
        if ($this->partialDir !== null) {
            return $this->partialDir;
        }

        return $this->getUploadDir().DIRECTORY_SEPARATOR.'.partial';
    }

    /**
     * Receives a single chunk and appends it to the partial file.
     *
     * @param string $uploadId Unique upload session identifier.
     * @param string|null $filename The filename to use. If null, attempts to
     *        read from X-Filename header or Content-Disposition header.
     * @param bool $isLast Whether this is the final chunk.
     *
     * @return array{offset: int, complete: bool, file: ?UploadedFile}
     *
     * @throws FileException If validation fails or the file cannot be written.
     */
    public function receiveChunk(string $uploadId, ?string $filename = null, bool $isLast = false): array {
        if (strlen($uploadId) === 0) {
            throw new FileException('Upload ID cannot be empty.');
        }

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

        $partialPath = $this->getPartialPath($uploadId, $filename);
        $isFirstChunk = !file_exists($partialPath);

        if ($isFirstChunk) {
            $fileInfo = [
                'name' => $filename,
                'upload-path' => $this->getUploadDir(),
                'upload-id' => $uploadId,
            ];

            if (!$this->fireBeforeUpload($fileInfo)) {
                throw new FileException('Upload rejected by callback.');
            }
        }

        $maxSize = $this->getMaxFileSizeLimit();
        $currentOffset = $isFirstChunk ? 0 : filesize($partialPath);

        $dest = @fopen($partialPath, 'ab');

        if (!is_resource($dest)) {
            throw new FileException('Unable to open partial file for writing.');
        }

        try {
            $input = @fopen($this->inputSource, 'rb');

            if (!is_resource($input)) {
                throw new FileException('Unable to open input stream.');
            }

            try {
                while (!feof($input)) {
                    $chunk = fread($input, 8192);

                    if ($chunk === false || strlen($chunk) === 0) {
                        break;
                    }

                    $currentOffset += strlen($chunk);

                    if ($maxSize !== null && $currentOffset > $maxSize) {
                        fclose($input);
                        fclose($dest);
                        unlink($partialPath);

                        throw new FileException('File exceeds size limit.');
                    }

                    fwrite($dest, $chunk);
                }
            } finally {
                if (is_resource($input)) {
                    fclose($input);
                }
            }
        } finally {
            if (is_resource($dest)) {
                fclose($dest);
            }
        }

        $offset = file_exists($partialPath) ? filesize($partialPath) : 0;

        if ($isLast) {
            return $this->finalize($uploadId, $filename, $partialPath);
        }

        return [
            'offset' => $offset,
            'complete' => false,
            'file' => null,
        ];
    }

    /**
     * Sets the directory for storing partial (in-progress) uploads.
     *
     * If not set, defaults to `.partial/` inside the upload directory.
     *
     * @param string $dir Absolute path to the partial files directory.
     */
    public function setPartialDir(string $dir): void {
        $this->partialDir = $dir;
    }

    /**
     * Finalizes the upload by moving the partial file to the upload directory.
     *
     * @param string $uploadId Upload session ID.
     * @param string $filename The filename.
     * @param string $partialPath Full path to the partial file.
     *
     * @return array{offset: int, complete: bool, file: UploadedFile}
     *
     * @throws FileException If finalization fails.
     */
    private function finalize(string $uploadId, string $filename, string $partialPath): array {
        $finalPath = $this->getUploadDir().DIRECTORY_SEPARATOR.$filename;

        $processor = $this->getStreamProcessor();

        if ($processor !== null) {
            $stream = new FileStream($partialPath);
            $processor($stream->readChunks(), $finalPath);
            unlink($partialPath);
        } else {
            if (file_exists($finalPath)) {
                unlink($finalPath);
            }
            rename($partialPath, $finalPath);
        }

        $offset = file_exists($finalPath) ? filesize($finalPath) : 0;
        $file = new UploadedFile($filename, $this->getUploadDir());
        $file->setIsUploaded(true);
        $this->fireAfterUpload($file);

        return [
            'offset' => $offset,
            'complete' => true,
            'file' => $file,
        ];
    }

    /**
     * Returns the full path to the partial file.
     *
     * @param string $uploadId Upload session ID.
     * @param string $filename The filename.
     *
     * @return string Full path to the partial file.
     */
    private function getPartialPath(string $uploadId, string $filename): string {
        $partialDir = $this->getPartialDir();

        if (!is_dir($partialDir)) {
            mkdir($partialDir, 0755, true);
        }

        return $partialDir.DIRECTORY_SEPARATOR.$uploadId.'_'.$filename;
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
