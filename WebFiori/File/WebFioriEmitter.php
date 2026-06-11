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

use WebFiori\Http\Response;

/**
 * A ResponseEmitter that wraps WebFiori\Http\Response for framework integration.
 *
 * @author Ibrahim
 */
class WebFioriEmitter implements ResponseEmitter {
    private Response $response;

    public function __construct(?Response $response = null) {
        $this->response = $response ?? new Response();
    }

    /**
     * Returns the underlying Response object.
     *
     * @return Response
     */
    public function getResponse(): Response {
        return $this->response;
    }

    public function sendBody(\Generator $chunks): void {
        foreach ($chunks as $chunk) {
            $this->response->write($chunk);
        }
    }

    public function setHeader(string $name, string $value): void {
        $this->response->addHeader($name, $value);
    }

    public function setStatusCode(int $code): void {
        $this->response->setCode($code);
    }
}
