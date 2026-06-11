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
 * An interface that abstracts HTTP output for file serving.
 *
 * Implementations handle setting headers, status codes, and sending
 * the response body. This decouples file serving from any specific
 * HTTP framework.
 *
 * @author Ibrahim
 */
interface ResponseEmitter {
    /**
     * Sends the response body from a generator of chunks.
     *
     * @param \Generator $chunks A generator yielding string chunks.
     */
    public function sendBody(\Generator $chunks): void;
    /**
     * Sets an HTTP response header.
     *
     * @param string $name Header name.
     * @param string $value Header value.
     */
    public function setHeader(string $name, string $value): void;

    /**
     * Sets the HTTP response status code.
     *
     * @param int $code HTTP status code.
     */
    public function setStatusCode(int $code): void;
}
