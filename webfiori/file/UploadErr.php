<?php
namespace webfiori\file;

/**
 * A class which is used to hold errors in case of file with mappings to strings
 * values.
 *
 * @author Ibrahim
 */
class UploadErr {
    /**
     * A constant that is used to indicate upload directory does not exist.
     * It usually returned by some methods as error code.
     * @since 1.0
     */
    const NO_SUCH_DIR = 'no_such_dir';
    /**
     * A constant that is used to indicate uploaded file type is not allowed.
     * @since 1.0
     */
    const NOT_ALLOWED = 'not_allowed_type';
}
