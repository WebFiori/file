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
 * An interface that defines core file operations.
 *
 * This interface captures the essential behavior of a file object,
 * enabling dependency injection, mocking, and alternative implementations.
 *
 * @author Ibrahim
 */
interface FileInterface {
    /**
     * Returns the name of the file.
     *
     * @return string The name of the file including extension.
     */
    public function getName(): string;

    /**
     * Sets the name of the file.
     *
     * @param string $name The name of the file including extension.
     */
    public function setName(string $name): void;

    /**
     * Returns the directory at which the file exists.
     *
     * @return string The directory path.
     */
    public function getDir(): string;

    /**
     * Sets the directory at which the file exists.
     *
     * @param string $dir The directory path.
     *
     * @return bool True if set successfully, false otherwise.
     */
    public function setDir(string $dir): bool;

    /**
     * Returns the full absolute path to the file.
     *
     * @return string Full path including directory and name.
     */
    public function getAbsolutePath(): string;

    /**
     * Returns the file extension.
     *
     * @return string The file extension (e.g. 'txt', 'png').
     */
    public function getExtension(): string;

    /**
     * Returns the MIME type of the file.
     *
     * @return string MIME type string.
     */
    public function getMIME(): string;

    /**
     * Returns the size of the file in bytes.
     *
     * @return int|null Size in bytes, or null if unknown.
     */
    public function getSize(): ?int;

    /**
     * Checks if the file exists on the filesystem.
     *
     * @return bool True if the file exists.
     */
    public function isExist(): bool;

    /**
     * Returns the raw data of the file.
     *
     * @param bool $encode If true, returns base64-encoded data.
     *
     * @return string The raw file data.
     */
    public function getRawData(bool $encode = false): string;

    /**
     * Sets the raw data of the file.
     *
     * @param string $raw The raw data.
     * @param bool $decode If true, decodes from base64 before storing.
     * @param bool $strict If true, strict base64 decoding is used.
     */
    public function setRawData(string $raw, bool $decode = false, bool $strict = false): void;

    /**
     * Appends data to the file's in-memory content.
     *
     * @param string|array $data Data to append.
     */
    public function append(string|array $data): void;

    /**
     * Reads the file content from the filesystem.
     *
     * @param int $from Starting byte offset (-1 for beginning).
     * @param int $to Ending byte offset (-1 for end of file).
     */
    public function read(int $from = -1, int $to = -1): void;

    /**
     * Writes the file content to the filesystem.
     *
     * @param bool $append If true, appends to existing content.
     * @param bool $createIfNotExist If true, creates the file if it doesn't exist.
     */
    public function write(bool $append = true, bool $createIfNotExist = false): void;

    /**
     * Creates the file on the filesystem if it does not exist.
     *
     * @param bool $createDirIfNotExist If true, creates parent directories as needed.
     */
    public function create(bool $createDirIfNotExist = false): void;

    /**
     * Removes the file from the filesystem.
     *
     * @return bool True if successfully removed.
     */
    public function remove(): bool;
}
