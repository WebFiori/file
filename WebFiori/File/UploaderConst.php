<?php
namespace WebFiori\File;

/**
 * A class which is used to hold constants used by the class "FileUploader"
 *
 * @author Ibrahim
 */
class UploaderConst {
    /**
     * One of the constants which is used to initialize uploaded file array.
     */
    const ERR_INDEX = 'upload-error';
    /**
     * A constant that is used to indicate that original file was not created
     * from temporary uploaded file.
     */
    const ERR_MOVE_TEMP = 'temp_file_not_moved';
    /**
     * A constant that is used to indicate upload directory does not exist.
     */
    const ERR_NO_SUCH_DIR = 'no_such_dir';
    /**
     * A constant that is used to indicate uploaded file type is not allowed.
     */
    const ERR_NOT_ALLOWED = 'not_allowed_type';
    /**
     * A constant that is used to indicate uploaded file with same name was already uploaded.
     */
    const ALREADY_EXIST = 'already_uploaded';
    /**
     * One of the constants which is used to initialize uploaded file array.
     */
    const EXIST_INDEX = 'is-exist';
    /**
     * One of the constants which is used to initialize uploaded file array.
     */
    const MIME_INDEX = 'mime';
    /**
     * One of the constants which is used to initialize uploaded file array.
     */
    const NAME_INDEX = 'name';
    /**
     * One of the constants which is used to initialize uploaded file array.
     */
    const PATH_INDEX = 'upload-path';
    /**
     * One of the constants which is used to initialize uploaded file array.
     */
    const REPLACE_INDEX = 'is-replace';
    /**
     * One of the constants which is used to initialize uploaded file array.
     */
    const SIZE_INDEX = 'size';
    /**
     * One of the constants which is used to initialize uploaded file array.
     */
    const UPLOADED_INDEX = 'uploaded';
}
