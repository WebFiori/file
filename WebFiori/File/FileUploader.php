<?php
namespace WebFiori\File;

use WebFiori\File\Exceptions\FileException;
use WebFiori\Json\Json;
use WebFiori\Json\JsonI;
/**
 * A helper class that is used to upload most types of files to the server's file system.
 * 
 * The main aim of this class is to allow developer to upload files 
 * without having to deal directly with the array $_FILES. It can be used to 
 * perform the following tasks:
 * <ul>
 * <li>Upload one or multiple files.</li>
 * <li>Restrict the types of files which can be uploaded.</li>
 * <li>Store the uploaded file(s) to a specific location on the server.</li>
 * <li>View upload status of each file.</li>
 * </ul>
 * A basic example on how to use this class:
 * <pre>
 * $uploader = new FileUploader();
 * //allow png only
 * $uploader->addExt('png');
 * $uploader->setUploadDir('\home\my-site\uploads');
 * //the value of the attribute 'name' of file input
 * $uploader->setAssociatedFileName('user-files');
 * //upload files
 * $files = $uploader->upload();
 * //now we can check upload status of each file.
 * foreach($files as $fileArr){
 * //...
 * }
 * </pre>
 * 
 * @author Ibrahim
 * 
 */
class FileUploader extends AbstractUploader implements JsonI {
    /**
     * The name of the index at which the file is stored in the array <b>$_FILES</b>.
     * 
     * @var string
     * 
     */
    private $asscociatedName;
    /**
     * An array which contains uploaded files.
     * 
     * @var array
     * 
     */
    private $files;
    /**
     * Upload status message.
     * 
     * @var string
     * 
     */
    private $uploadStatusMessage;

    /**
     * Creates new instance of the class.
     *
     * @param string $uploadPath A string that represents the location at
     * which files will be uploaded to.
     *
     * @param array $allowedTypes An array that contains allowed files types. The
     * array can have values such as 'jpg', 'png', 'doc', etc...
     *
     * @throws FileException
     */
    public function __construct(string $uploadPath = '', array $allowedTypes = []) {
        parent::__construct($uploadPath, $allowedTypes);
        $this->uploadStatusMessage = 'NO ACTION';
        $this->files = [];
        $this->setAssociatedFileName('files');
    }
    /**
     * Returns a JSON string that represents the object.
     * 
     * The string will be something the the following:
     * <pre>
     * {
     * &nbsp&nbsp"upload-directory":"",
     * &nbsp&nbsp"allowed-types":[],
     * &nbsp&nbsp"files":[],
     * &nbsp&nbsp"associated-file-name":""
     * }
     * </pre>
     * 
     * @return string A JSON string.
     */
    public function __toString() {
        return $this->toJSON().'';
    }
    /**
     * Adds a file to the array $_FILES for testing files uploads.
     * 
     * This method can be used to simulate the process of single file upload.
     * 
     * @param string $fileIdx The name of the index that will hold the blob.
     * This is usually represented by the attribute 'name' of file input in
     * the front-end.
     * 
     * @param string $filePath The path of the file within testing environment.
     * 
     * @param bool $reset If set to true, the array $_FILES will be re-initialized.
     */
    public static function addTestFile(string $fileIdx = '', string $filePath = '', bool $reset = false) {
        if ($reset) {
            $_FILES = [];
        }

        $trimmed = trim($fileIdx);

        if (strlen($trimmed) == 0) {
            return;
        }

        if (!isset($_FILES[$trimmed])) {
            $_FILES[$trimmed] = [];
            $_FILES[$trimmed]['name'] = [];
            $_FILES[$trimmed]['type'] = [];
            $_FILES[$trimmed]['size'] = [];
            $_FILES[$trimmed]['tmp_name'] = [];
            $_FILES[$trimmed]['error'] = [];
        }
        $info = File::extractPathAndName($filePath);
        $path = $info['path'].DIRECTORY_SEPARATOR.$info['name'];

        if (!File::isFileExist($path)) {
            throw new FileException('No file was found at \''.$path.'\'.');
        }

        $nameExpl = explode('.', $info['name']);

        if (count($nameExpl) == 2) {
            $ext = $nameExpl[1];
        } else {
            $ext = 'bin';
        }

        $_FILES[$trimmed]['name'][] = $info['name'];
        $_FILES[$trimmed]['type'][] = MIME::getType($ext);
        $_FILES[$trimmed]['size'][] = filesize($path);
        $_FILES[$trimmed]['tmp_name'][] = $path;
        $_FILES[$trimmed]['error'][] = 0;
    }

    /**
     * Returns the name of the index at which the uploaded files will exist on in the array $_FILES.
     * 
     * This value represents the value of the attribute 'name' of the files input 
     * in case of HTML forms.
     * 
     * @return string the name of the index at which the uploaded files will exist on in the array $_FILES.
     * Default value is 'files'.
     */
    public function getAssociatedFileName() : string {
        return $this->asscociatedName;
    }
    /**
     * Returns an array which contains all information about the uploaded files.
     * 
     * The returned array will be indexed. At each index, a sub associative array 
     * that holds uploaded file information. Each array will have the following 
     * indices:
     * <ul>
     * <li><b>name</b>: The name of the uploaded file.</li>
     * <li><b>size</b>: The size of the uploaded file in bytes.</li>
     * <li><b>upload-path</b>: The location at which the file was uploaded to in the server.</li>
     * <li><b>upload-error</b>: Any error which has happened during upload.</li>
     * <li><b>is-exist</b>: A boolean. Set to true if the file does exist in the server.</li>
     * <li><b>is-replace</b>: A boolean. Set to true if the file was already uploaded and replaced.</li>
     * <li><b>mime</b>: MIME type of the file.</li>
     * <li><b>uploaded</b>: A boolean. Set to true if the file was uploaded.</li>
     * </ul>
     * 
     * @param bool $asObj If this parameter is set to true, the returned array
     * will contain objects of type 'UploadedFile' instead of sub associative arrays. 
     * Default value is true.
     * 
     * @return array An indexed array that contains sub associative arrays or 
     * objects of type FileInterface.
     * 
     * 
     */
    public function getFiles(bool $asObj = true) : array {
        $asObjC = $asObj === true;
        $retVal = [];

        if ($asObjC) {
            foreach ($this->files as $fArr) {
                $retVal[] = $this->createFileObjFromArray($fArr);
            }
        } else {
            $retVal = $this->files;
        }

        return $retVal;
    }
    /**
     * Returns the value of the directive 'upload_max_filesize' in KB.
     * 
     * @return int
     */
    public static function getMaxFileSize() : int {
        $val = ini_get('upload_max_filesize');
        $lastChar = strtoupper($val[strlen($val) - 1]);

        switch ($lastChar) {
            case 'M' : {
                return intval($val) * 1024;
            } case 'K' : {
                return intval($val);
            } case 'G' : {
                return intval($val) * 1048576;
            } default : {
                return intval($val) / 1024;
            }
        }
    }
    /**
     * Sets The name of the index at which the file is stored in the array $_FILES.
     * 
     * This value is the value of the attribute 'name' in case of HTML file input. 
     * 
     * It is possible to set the value of the property in the front end by using a 
     * hidden input field with name = 'file' and the value of that input 
     * field must be the value of the attribute 'name' of the original file input. 
     * In case of API call, it can be supplied as a POST parameter with name 
     * 'file'.
     * 
     * @param string $name The name of the index at which the file is stored in the array $_FILES.
     * input element.
     * 
     */
    public function setAssociatedFileName(string $name) {
        $trimmed = trim($name);

        if (strlen($trimmed) != 0) {
            $this->asscociatedName = $trimmed;
        }
    }
    /**
     * Returns a JSON representation of the object.
     * 
     * @return Json an object of type <b>Json</b>
     * 
     */
    public function toJSON() : Json {
        $j = new Json();
        $j->add('uploadDirectory', $this->getUploadDir());
        $j->add('associatedFileName', $this->getAssociatedFileName());
        $j->add('allowedTypes', $this->getExts());
        $fsArr = [];

        foreach ($this->getFiles() as $fArr) {
            $fsArr[] = $fArr;
        }
        $j->add('files', $fsArr);

        return $j;
    }

    /**
     * Upload the file to the server.
     *
     * @param bool $replaceIfExist If a file with the given name found
     * and this parameter is set to true, the file will be replaced.
     *
     * @return array An array which contains uploaded files info. Each index
     * will contain an associative array which has the following info:
     * <ul>
     * <li><b>name</b>: The name of uploaded file.</li>
     * <li><b>size</b>: The size of uploaded file in bytes.</li>
     * <li><b>upload-path</b>: The location at which the file was uploaded to in the server.</li>
     * <li><b>upload-error</b>: A string that represents upload error.</li>
     * <li><b>is-exist</b>: A boolean. Set to true if the file was found in the
     * server.</li>
     * <li><b>is-replace</b>: A boolean. Set to true if the file was exist and replaced.</li>
     * <li><b>mime</b>: MIME type of the file.</li>
     * <li><b>uploaded</b>: A boolean. Set to true if the file was uploaded.</li>
     * </ul>
     *
     * @throws FileException If the path for uploading files is not set.
     */
    public function upload(bool $replaceIfExist = false) : array {
        $this->files = [];

        if (strlen($this->getUploadDir()) == 0) {
            throw new FileException('Upload path is not set.');
        }

        $meth = getenv('REQUEST_METHOD');

        if ($meth === false) {
            $meth = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
        }
        $reqMeth = filter_var($meth, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($reqMeth) == 'POST') {
            $fileOrFiles = null;
            $associatedInputName = filter_input(INPUT_POST, 'file');

            if (gettype($associatedInputName) != 'string' && isset($_POST['file'])) {
                //Probably in cli (test env)
                $associatedInputName = filter_var($_POST['file']);
            }

            if ($associatedInputName !== null) {
                $this->setAssociatedFileName($associatedInputName);
            }

            if (isset($_FILES[$this->getAssociatedFileName()])) {
                $fileOrFiles = $_FILES[$this->getAssociatedFileName()];
            }

            if ($fileOrFiles !== null) {
                if (gettype($fileOrFiles['name']) == 'array') {
                    //multi-upload
                    $filesCount = count($fileOrFiles['name']);

                    for ($x = 0 ; $x < $filesCount ; $x++) {
                        $fileInfoArr = $this->getFileArr($fileOrFiles, $replaceIfExist, $x);
                        $this->files[] = $fileInfoArr;
                    }
                } else {
                    //single file upload
                    $fileInfoArr = $this->getFileArr($fileOrFiles, $replaceIfExist, null);
                    $this->files[] = $fileInfoArr;
                }
            }
        }

        return $this->files;
    }

    /**
     * Returns an array that contains objects of type 'UploadedFile'.
     *
     * @param bool $replaceIfExist If a file with the given name found
     * and this parameter is set to true, the file will be replaced.
     *
     * @return array An array that contains objects of type FileInterface.
     *
     * @throws FileException
     */
    public function uploadAsFileObj(bool $replaceIfExist = false) : array {
        $uploadedFiles = $this->upload($replaceIfExist);
        $filesArr = [];

        foreach ($uploadedFiles as $fileArray) {
            $filesArr[] = $this->createFileObjFromArray($fileArray);
        }

        return $filesArr;
    }
    private function createFileObjFromArray($arr) : UploadedFile {
        $fName = filter_var($arr[UploaderConst::NAME_INDEX], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $fPath = $arr[UploaderConst::PATH_INDEX];

        $file = new UploadedFile($fName, $fPath);
        $file->setMIME($arr[UploaderConst::MIME_INDEX]);

        if (isset($arr[UploaderConst::REPLACE_INDEX])) {
            $file->setIsReplace($arr[UploaderConst::REPLACE_INDEX]);
        }
        $file->setIsUploaded($arr[UploaderConst::UPLOADED_INDEX]);
        $file->setUploadErr($arr[UploaderConst::ERR_INDEX]);

        return $file;
    }
    /**
     * Processes file upload data and creates file information array.
     * 
     * This method handles both single and multiple file uploads, performing
     * validation, error checking, and file movement operations.
     * 
     * @param array $fileOrFiles The file data from $_FILES array.
     * @param bool $replaceIfExist Whether to replace existing files.
     * @param string|null $idx The index for multiple file uploads, null for single file.
     * 
     * @return array An associative array containing file upload information.
     */
    private function getFileArr($fileOrFiles,$replaceIfExist, ?int $idx): array {
        $errIdx = 'error';
        $tempIdx = 'tmp_name';
        $fileInfoArr = [];
        $fileInfoArr[UploaderConst::NAME_INDEX] = $idx === null ? filter_var($fileOrFiles[UploaderConst::NAME_INDEX]) : filter_var($fileOrFiles[UploaderConst::NAME_INDEX][$idx]);
        $fileInfoArr[UploaderConst::NAME_INDEX] = self::sanitizeFilename($fileInfoArr[UploaderConst::NAME_INDEX]);
        $fileInfoArr[UploaderConst::SIZE_INDEX] = $idx === null ? filter_var($fileOrFiles[UploaderConst::SIZE_INDEX], FILTER_SANITIZE_NUMBER_INT) : filter_var($fileOrFiles[UploaderConst::SIZE_INDEX][$idx], FILTER_SANITIZE_NUMBER_INT);
        $fileInfoArr[UploaderConst::PATH_INDEX] = $this->getUploadDir();
        $fileInfoArr[UploaderConst::ERR_INDEX] = '';
        $nameSplit = explode('.', $fileInfoArr[UploaderConst::NAME_INDEX]);
        $fileInfoArr[UploaderConst::MIME_INDEX] = MIME::getType($nameSplit[count($nameSplit) - 1]);
        $fileInfoArr[UploaderConst::UPLOADED_INDEX] = false;

        $isErr = $idx === null ? $this->isError($fileOrFiles[$errIdx]) : $this->isError($fileOrFiles[$errIdx][$idx]);

        if (!$isErr) {
            if ($this->isValidExt($fileInfoArr[UploaderConst::NAME_INDEX])) {
                $maxSize = $this->getMaxFileSizeLimit();

                if ($maxSize !== null && (int)$fileInfoArr[UploaderConst::SIZE_INDEX] > $maxSize) {
                    $fileInfoArr[UploaderConst::UPLOADED_INDEX] = false;
                    $fileInfoArr[UploaderConst::ERR_INDEX] = UploaderConst::ERR_FILE_TOO_LARGE;
                } else if (File::isDirectory($this->getUploadDir())) {
                    if (!$this->fireBeforeUpload($fileInfoArr)) {
                        $fileInfoArr[UploaderConst::UPLOADED_INDEX] = false;
                        $fileInfoArr[UploaderConst::ERR_INDEX] = 'rejected_by_callback';
                    } else {
                        $filePath = $this->getUploadDir().DIRECTORY_SEPARATOR.$fileInfoArr[UploaderConst::NAME_INDEX];

                        if (!File::isFileExist($filePath)) {
                            $fileInfoArr[UploaderConst::EXIST_INDEX] = false;
                            $fileInfoArr[UploaderConst::REPLACE_INDEX] = false;
                            $name = $idx === null ? $fileOrFiles[$tempIdx] : $fileOrFiles[$tempIdx][$idx];
                            $sanitizedName = filter_var($name);

                            if (!$this->moveFile($sanitizedName, $filePath)) {
                                $fileInfoArr[UploaderConst::ERR_INDEX] = UploaderConst::ERR_MOVE_TEMP;
                            } else {
                                $fileInfoArr[UploaderConst::UPLOADED_INDEX] = true;
                            }
                        } else {
                            $fileInfoArr[UploaderConst::EXIST_INDEX] = true;

                            if ($replaceIfExist) {
                                $fileInfoArr[UploaderConst::REPLACE_INDEX] = true;
                                unlink($filePath);
                                $name = $idx === null ? $fileOrFiles[$tempIdx] : $fileOrFiles[$tempIdx][$idx];
                                $sanitizedName = filter_var($name);

                                if ($this->moveFile($sanitizedName, $filePath)) {
                                    $fileInfoArr[UploaderConst::UPLOADED_INDEX] = true;
                                } else {
                                    $fileInfoArr[UploaderConst::UPLOADED_INDEX] = false;
                                }
                            } else {
                                $fileInfoArr[UploaderConst::REPLACE_INDEX] = false;
                                $fileInfoArr[UploaderConst::ERR_INDEX] = UploaderConst::ALREADY_EXIST;
                            }
                        }
                    }
                } else {
                    $fileInfoArr[UploaderConst::ERR_INDEX] = UploaderConst::ERR_NO_SUCH_DIR;
                    $fileInfoArr[UploaderConst::UPLOADED_INDEX] = false;
                }
            } else {
                $fileInfoArr[UploaderConst::UPLOADED_INDEX] = false;
                $fileInfoArr[UploaderConst::ERR_INDEX] = UploaderConst::ERR_NOT_ALLOWED;
            }
        } else {
            $fileInfoArr[UploaderConst::UPLOADED_INDEX] = false;
            $fileInfoArr[UploaderConst::ERR_INDEX] = $idx === null ? $fileOrFiles[$errIdx] : $fileOrFiles[$errIdx][$idx];
        }

        if ($fileInfoArr[UploaderConst::UPLOADED_INDEX]) {
            $this->fireAfterUpload($this->createFileObjFromArray($fileInfoArr));
        }

        return $fileInfoArr;
    }
    /**
     * 
     * @param int $code PHP upload code.
     * 
     * @return bool If the given code does not equal to UPLOAD_ERR_OK, the
     * method will return true.
     * 
     */
    private function isError(int $code): bool {
        switch ($code) {
            case UPLOAD_ERR_OK:{
                $this->uploadStatusMessage = 'File Uploaded';

                return false;
            }
            case UPLOAD_ERR_INI_SIZE:{
                $this->uploadStatusMessage = 'File Size is Larger Than '.(self::getMaxFileSize()).'KB. Found in php.ini.';
                break;
            }
            case UPLOAD_ERR_PARTIAL:{
                $this->uploadStatusMessage = 'File Uploaded Partially';
                break;
            }
            case UPLOAD_ERR_NO_FILE:{
                $this->uploadStatusMessage = 'No File was Uploaded';
                break;
            }
            case UPLOAD_ERR_NO_TMP_DIR:{
                $this->uploadStatusMessage = 'Temporary Folder is Missing';
                break;
            }
            case UPLOAD_ERR_CANT_WRITE:{
                $this->uploadStatusMessage = 'Faild to Write File to Disk';
                break;
            }
            default :{
                $this->uploadStatusMessage = 'No File was Uploaded due to uknown error';
            }
        }

        return true;
    }
    /**
     * Moves or streams a file from source to destination.
     *
     * If a stream processor is set, uses FileStream to pipe chunks through
     * the processor. Otherwise uses move_uploaded_file or copy.
     *
     * @param string $source Source file path.
     * @param string $dest Destination file path.
     *
     * @return bool True on success.
     */
    private function moveFile(string $source, string $dest): bool {
        $processor = $this->getStreamProcessor();

        if ($processor !== null) {
            try {
                $stream = new FileStream($source);
                $processor($stream->readChunks(), $dest);

                return true;
            } catch (\Throwable $e) {
                return false;
            }
        }

        $moveFunc = http_response_code() === false ? 'copy' : 'move_uploaded_file';

        return $moveFunc($source, $dest);
    }
}
