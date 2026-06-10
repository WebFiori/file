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
 * A streaming file class that processes files in constant memory.
 *
 * Unlike the File class which loads entire content into memory,
 * FileStream uses generators to read and write data in fixed-size
 * buffers, making it suitable for large files.
 *
 * @author Ibrahim
 */
class FileStream implements StreamableInterface {
    private string $path;
    private int $bufferSize;

    /**
     * Creates a new FileStream instance.
     *
     * @param string $path Absolute path to the file.
     * @param int $bufferSize Default buffer size for read operations.
     *
     * @throws FileException If path is empty.
     */
    public function __construct(string $path, int $bufferSize = 8192) {
        $path = File::fixPath($path);

        if (strlen($path) === 0) {
            throw new FileException('File path cannot be empty.');
        }
        $this->path = $path;
        $this->setBufferSize($bufferSize);
    }

    public function readChunks(int $bufferSize = 8192): \Generator {
        $bufferSize = $bufferSize > 0 ? $bufferSize : $this->bufferSize;
        $resource = $this->openOrFail('rb');

        try {
            while (!feof($resource)) {
                $chunk = fread($resource, $bufferSize);

                if ($chunk === false || strlen($chunk) === 0) {
                    break;
                }
                yield $chunk;
            }
        } finally {
            fclose($resource);
        }
    }

    public function readLines(): \Generator {
        $resource = $this->openOrFail('rb');

        try {
            while (($line = fgets($resource)) !== false) {
                yield $line;
            }
        } finally {
            fclose($resource);
        }
    }

    public function readRange(int $from, int $to, int $bufferSize = 8192): \Generator {
        if ($from < 0 || $to < $from) {
            throw new FileException('Invalid range: from must be >= 0 and to must be >= from.');
        }
        $bufferSize = $bufferSize > 0 ? $bufferSize : $this->bufferSize;
        $resource = $this->openOrFail('rb');

        try {
            fseek($resource, $from);
            $remaining = $to - $from;

            while ($remaining > 0 && !feof($resource)) {
                $readSize = min($bufferSize, $remaining);
                $chunk = fread($resource, $readSize);

                if ($chunk === false || strlen($chunk) === 0) {
                    break;
                }
                $remaining -= strlen($chunk);
                yield $chunk;
            }
        } finally {
            fclose($resource);
        }
    }

    public function writeFromStream(iterable $source, bool $append = true, bool $lock = true): void {
        $mode = $append ? 'ab' : 'wb';
        $resource = $this->openOrFail($mode);

        try {
            if ($lock && !flock($resource, LOCK_EX)) {
                throw new FileException('Unable to acquire lock on \'' . $this->path . '\'.');
            }

            foreach ($source as $chunk) {
                fwrite($resource, $chunk);
            }
            fflush($resource);

            if ($lock) {
                flock($resource, LOCK_UN);
            }
        } finally {
            fclose($resource);
        }
    }

    public function serve(bool $asAttachment = false, ?ResponseEmitter $emitter = null): void {
        if (!File::isFileExist($this->path)) {
            throw new FileException('File not found: \'' . $this->path . '\'.');
        }

        $emitter = $emitter ?? new DefaultEmitter();
        $emitter->setStatusCode(200);
        $emitter->setHeader('Accept-Ranges', 'bytes');
        $emitter->setHeader('Content-Type', $this->getMIME());
        $emitter->setHeader('Content-Length', (string) $this->getSize());

        $disposition = $asAttachment ? 'attachment' : 'inline';
        $emitter->setHeader('Content-Disposition', $disposition . '; filename="' . $this->getName() . '"');

        $emitter->sendBody($this->readChunks($this->bufferSize));
    }

    public function getSize(): int {
        if (!File::isFileExist($this->path)) {
            return 0;
        }

        return filesize($this->path);
    }

    public function getMIME(): string {
        $ext = pathinfo($this->path, PATHINFO_EXTENSION);

        return MIME::getType($ext);
    }

    public function getName(): string {
        return basename($this->path);
    }

    /**
     * Returns the default buffer size.
     *
     * @return int Buffer size in bytes.
     */
    public function getBufferSize(): int {
        return $this->bufferSize;
    }

    /**
     * Sets the default buffer size.
     *
     * @param int $size Buffer size in bytes. Must be greater than 0.
     */
    public function setBufferSize(int $size): void {
        $this->bufferSize = $size > 0 ? $size : 8192;
    }

    /**
     * Returns the absolute path to the file.
     *
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }

    /**
     * Opens the file or throws an exception.
     *
     * @param string $mode fopen mode.
     *
     * @return resource
     *
     * @throws FileException
     */
    private function openOrFail(string $mode) {
        $resource = File::createResource($mode, $this->path);

        if (!is_resource($resource)) {
            throw new FileException('Unable to open file: \'' . $this->path . '\'.');
        }

        return $resource;
    }
}
