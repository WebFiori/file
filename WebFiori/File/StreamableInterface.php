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

/**
 * An interface that defines streaming file I/O operations.
 *
 * Implementations process files in constant memory using generators,
 * making them suitable for large files that cannot be loaded entirely
 * into memory.
 *
 * @author Ibrahim
 */
interface StreamableInterface {
    /**
     * Reads the file in fixed-size chunks.
     *
     * @param int $bufferSize The size of each chunk in bytes.
     *
     * @return \Generator Yields string chunks.
     */
    public function readChunks(int $bufferSize = 8192): \Generator;

    /**
     * Reads the file line by line.
     *
     * @return \Generator Yields one line at a time.
     */
    public function readLines(): \Generator;

    /**
     * Reads a specific byte range from the file.
     *
     * @param int $from Starting byte offset.
     * @param int $to Ending byte offset.
     * @param int $bufferSize The size of each chunk in bytes.
     *
     * @return \Generator Yields string chunks within the range.
     */
    public function readRange(int $from, int $to, int $bufferSize = 8192): \Generator;

    /**
     * Writes data from an iterable source to the file.
     *
     * @param iterable $source An iterable yielding string chunks.
     * @param bool $append If true, appends to the file.
     * @param bool $lock If true, acquires an exclusive lock during write.
     */
    public function writeFromStream(iterable $source, bool $append = true, bool $lock = true): void;

    /**
     * Serves the file over HTTP using a ResponseEmitter.
     *
     * @param bool $asAttachment If true, triggers download dialog.
     * @param ResponseEmitter|null $emitter The emitter to use. If null, DefaultEmitter is used.
     */
    public function serve(bool $asAttachment = false, ?ResponseEmitter $emitter = null): void;

    /**
     * Returns the file size in bytes.
     *
     * @return int Size in bytes.
     */
    public function getSize(): int;

    /**
     * Returns the MIME type of the file.
     *
     * @return string MIME type.
     */
    public function getMIME(): string;

    /**
     * Returns the name of the file.
     *
     * @return string File name.
     */
    public function getName(): string;
}
