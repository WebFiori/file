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
 * A default ResponseEmitter that uses raw PHP header() and echo for output.
 *
 * @author Ibrahim
 */
class DefaultEmitter implements ResponseEmitter {
    public function setHeader(string $name, string $value): void {
        header("$name: $value");
    }

    public function setStatusCode(int $code): void {
        http_response_code($code);
    }

    public function sendBody(\Generator $chunks): void {
        foreach ($chunks as $chunk) {
            if (connection_aborted()) {
                break;
            }
            echo $chunk;
            flush();
        }
    }
}
