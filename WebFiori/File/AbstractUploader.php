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
 * Base class for file uploaders providing shared validation, extension filtering,
 * size limits, stream processing, and callback hooks.
 *
 * @author Ibrahim
 */
abstract class AbstractUploader {
    /**
     * @var array
     */
    private $extensions = [];
    /**
     * @var string
     */
    private $uploadDir;
    /**
     * @var int|null
     */
    private $maxFileSize;
    /**
     * @var callable|null
     */
    private $streamProcessor;
    /**
     * @var callable|null
     */
    private $onBeforeUpload;
    /**
     * @var callable|null
     */
    private $onAfterUpload;

    public function __construct(string $uploadPath = '', array $allowedTypes = []) {
        $this->maxFileSize = null;
        $this->streamProcessor = null;
        $this->onBeforeUpload = null;
        $this->onAfterUpload = null;
        $this->uploadDir = '';
        $this->addExts($allowedTypes);

        if (strlen($uploadPath) != 0) {
            $this->setUploadDir($uploadPath);
        }
    }

    /**
     * Adds new extension to the array of allowed files types.
     *
     * @param string $ext File extension (e.g. jpg, png, pdf).
     *
     * @return bool If the extension is added, the method will return true.
     */
    public function addExt(string $ext): bool {
        $ext = str_replace('.', '', $ext);
        $len = strlen($ext);
        $retVal = true;

        if ($len != 0) {
            for ($x = 0; $x < $len; $x++) {
                $ch = $ext[$x];

                if (!($ch == '_' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9'))) {
                    $retVal = false;
                    break;
                }
            }

            if ($retVal === true) {
                $this->extensions[] = $ext;
            }
        } else {
            $retVal = false;
        }

        return $retVal;
    }

    /**
     * Adds multiple extensions at once to the set of allowed files types.
     *
     * @param array $arr An array of strings. Each string represents a file type.
     *
     * @return array Associative array of extension => bool.
     */
    public function addExts(array $arr): array {
        $retVal = [];

        foreach ($arr as $ext) {
            $retVal[$ext] = $this->addExt($ext);
        }

        return $retVal;
    }

    /**
     * Returns the array that contains all allowed file types.
     *
     * @return array
     */
    public function getExts(): array {
        return $this->extensions;
    }

    /**
     * Removes an extension from the array of allowed files types.
     *
     * @param string $ext File extension (e.g. jpg, png, pdf).
     *
     * @return bool If the extension was removed, the method will return true.
     */
    public function removeExt(string $ext): bool {
        $exts = $this->getExts();
        $count = count($exts);
        $retVal = false;
        $temp = [];
        $ext = str_replace('.', '', $ext);

        for ($x = 0; $x < $count; $x++) {
            if ($exts[$x] != $ext) {
                $temp[] = $exts[$x];
            } else {
                $retVal = true;
            }
        }
        $this->extensions = $temp;

        return $retVal;
    }

    /**
     * Returns the directory at which the file or files will be uploaded to.
     *
     * @return string upload directory.
     */
    public function getUploadDir(): string {
        return $this->uploadDir;
    }

    /**
     * Sets the directory at which the file will be uploaded to.
     *
     * @param string $dir Upload Directory.
     *
     * @throws FileException If given directory is invalid.
     */
    public function setUploadDir(string $dir): void {
        $fixedPath = File::fixPath($dir);

        if (strlen($fixedPath) == 0) {
            throw new FileException('Upload directory should not be an empty string.');
        }

        try {
            if (!File::isDirectory($fixedPath)) {
                throw new FileException('Invalid upload directory: ' . $fixedPath);
            }

            $this->uploadDir = $fixedPath;
        } catch (FileException $ex) {
            throw new FileException('Invalid upload directory: ' . $fixedPath);
        }
    }

    /**
     * Sets a custom maximum file size limit.
     *
     * @param int $bytes Maximum file size in bytes.
     */
    public function setMaxFileSize(int $bytes): void {
        if ($bytes > 0) {
            $this->maxFileSize = $bytes;
        }
    }

    /**
     * Returns the custom maximum file size limit.
     *
     * @return int|null The limit in bytes, or null if not set.
     */
    public function getMaxFileSizeLimit(): ?int {
        return $this->maxFileSize;
    }

    /**
     * Sets a stream processor for upload processing.
     *
     * @param callable|null $processor Signature: function(\Generator $chunks, string $destPath): void
     */
    public function setStreamProcessor(?callable $processor): void {
        $this->streamProcessor = $processor;
    }

    /**
     * Returns the current stream processor.
     *
     * @return callable|null
     */
    public function getStreamProcessor(): ?callable {
        return $this->streamProcessor;
    }

    /**
     * Sets a callback to be called before each file is uploaded.
     *
     * @param callable|null $callback Signature: function(array $fileInfo): bool
     */
    public function setOnBeforeUpload(?callable $callback): void {
        $this->onBeforeUpload = $callback;
    }

    /**
     * Returns the before-upload callback.
     *
     * @return callable|null
     */
    public function getOnBeforeUpload(): ?callable {
        return $this->onBeforeUpload;
    }

    /**
     * Sets a callback to be called after each successful file upload.
     *
     * @param callable|null $callback Signature: function(UploadedFile $file): void
     */
    public function setOnAfterUpload(?callable $callback): void {
        $this->onAfterUpload = $callback;
    }

    /**
     * Returns the after-upload callback.
     *
     * @return callable|null
     */
    public function getOnAfterUpload(): ?callable {
        return $this->onAfterUpload;
    }

    /**
     * Sanitizes an uploaded filename.
     *
     * @param string $name The raw filename.
     *
     * @return string The sanitized filename.
     */
    public static function sanitizeFilename(string $name): string {
        $name = str_replace("\\", "/", $name);
        $name = basename($name);
        $name = str_replace("\0", '', $name);
        $name = preg_replace('/[^\w\-. ]/', '_', $name);
        $name = ltrim($name, '.');

        return $name;
    }

    /**
     * Checks if a filename has an allowed extension.
     *
     * @param string $fileName The filename to check.
     *
     * @return bool True if allowed.
     */
    protected function isValidExt(string $fileName): bool {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        return in_array($ext, $this->getExts(), true) || in_array(strtolower($ext), $this->getExts(), true);
    }

    /**
     * Invokes the before-upload callback.
     *
     * @param array $fileInfo File information array.
     *
     * @return bool True if upload should proceed.
     */
    protected function fireBeforeUpload(array $fileInfo): bool {
        if ($this->onBeforeUpload !== null) {
            return ($this->onBeforeUpload)($fileInfo) !== false;
        }

        return true;
    }

    /**
     * Invokes the after-upload callback.
     *
     * @param UploadedFile $file The uploaded file.
     */
    protected function fireAfterUpload(UploadedFile $file): void {
        if ($this->onAfterUpload !== null) {
            ($this->onAfterUpload)($file);
        }
    }
}
