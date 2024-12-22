<?php
namespace webfiori\file;

use webfiori\file\exceptions\FileException;
use webfiori\json\Json;
use webfiori\json\JsonI;
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
 * $uploader = new Uploader();
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
class FileUploader implements JsonI {
    /**
     * The name of the index at which the file is stored in the array <b>$_FILES</b>.
     * 
     * @var string
     * 
     * @since 1.0
     */
    private $asscociatedName;
    /**
     * An array that contains all the allowed file types.
     * 
     * @var array An array of strings. 
     * 
     * @since 1.0
     */
    private $extentions = [];
    /**
     * An array which contains uploaded files.
     * 
     * @var array
     * 
     * @since 1.0 
     */
    private $files;
    /**
     * The directory at which the file (or files) will be uploaded to.
     * 
     * @var string A directory. 
     * 
     * @since 1.0
     */
    private $uploadDir;
    /**
     * Upload status message.
     * 
     * @var string
     * 
     * @since 1.0 
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
     * @since 1.0
     */
    public function __construct(string $uploadPath = '', array $allowedTypes = []) {
        $this->uploadStatusMessage = 'NO ACTION';
        $this->files = [];
        $this->setAssociatedFileName('files');
        $this->addExts($allowedTypes);
        $this->uploadDir = '';

        if (strlen($uploadPath) != 0) {
            $this->setUploadDir($uploadPath);
        }
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
     * Adds new extension to the array of allowed files types.
     * 
     * @param string $ext File extension (e.g. jpg, png, pdf).
     * 
     * @return bool If the extension is added, the method will return true.
     * 
     * @since 1.0
     */
    public function addExt(string $ext) : bool {
        $ext = str_replace('.', '', $ext);
        $len = strlen($ext);
        $retVal = true;

        if ($len != 0) {
            for ($x = 0 ; $x < $len ; $x++) {
                $ch = $ext[$x];

                if (!($ch == '_' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9'))) {
                    $retVal = false;
                    break;
                }
            }

            if ($retVal === true) {
                $this->extentions[] = $ext;
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
     * @return array The method will return an associative array of booleans. 
     * The key value will be the extension name and the value represents the status 
     * of the addition. If added, it will be set to true.
     * 
     * @since 1.2.2
     */
    public function addExts(array $arr) : array {
        $retVal = [];

        foreach ($arr as $ext) {
            $retVal[$ext] = $this->addExt($ext);
        }

        return $retVal;
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
        $info = self::extractPathAndName($filePath);
        $path = $info['path'].DS.$info['name'];
        
        
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
     * Returns the array that contains all allowed file types.
     * 
     * @return array
     * 
     * @since 1.0
     */
    public function getExts() : array {
        return $this->extentions;
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
     * objects of type 'UploadFile'.
     * 
     * @since 1.0
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
     * Returns the directory at which the file or files will be uploaded to.
     * 
     * @return string upload directory. Default return value is empty string.
     * 
     * @since 1.0
     */
    public function getUploadDir() : string {
        return $this->uploadDir;
    }
    /**
     * Removes an extension from the array of allowed files types.
     * 
     * @param string $ext File extension= (e.g. jpg, png, pdf,...).
     * 
     * @return bool If the extension was removed, the method will return true.
     * 
     * @since 1.0
     */
    public function removeExt(string $ext) : bool {
        $exts = $this->getExts();
        $count = count($exts);
        $retVal = false;
        $temp = [];
        $ext = str_replace('.', '', $ext);

        for ($x = 0 ; $x < $count ; $x++) {
            if ($exts[$x] != $ext) {
                $temp[] = $exts[$x];
            } else {
                $retVal = true;
            }
        }
        $this->extentions = $temp;

        return $retVal;
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
     * @since 1.0
     */
    public function setAssociatedFileName(string $name) {
        $trimmed = trim($name);

        if (strlen($trimmed) != 0) {
            $this->asscociatedName = $trimmed;
        }
    }
    /**
     * Sets the directory at which the file will be uploaded to.
     * 
     * This method will first check whether the directory is exist or not. then 
     * it validate that the structure of the path is valid by replacing 
     * forward slashes with backward slashes.
     * 
     * @param string $dir Upload Directory (such as '/files/uploads' or 
     * 'C:/Server/uploads'). 
     * 
     * 
     * @throws FileException If given directory is invalid or was not set.
     * 
     * @since 1.0
     */
    public function setUploadDir(string $dir) {
        $fixedPath = File::fixPath($dir);

        $dir = str_replace('/', '\\', $fixedPath);

        if (strlen($dir) == 0) {
            throw new FileException('Upload directory should not be an empty string.');
        }
        try {
            $this->uploadDir = !File::isDirectory($fixedPath) ? '\\'.$fixedPath : $fixedPath;

            if (!File::isDirectory($this->uploadDir)) {
                throw new FileException('Invalid upload directory: '.$this->uploadDir);
            }
        } catch (FileException $ex) {
            throw new FileException('Invalid upload directory: '.$dir);
        }
    }
    /**
     * Returns a JSON representation of the object.
     * 
     * @return Json an object of type <b>Json</b>
     * 
     * @since 1.0
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
     * @since 1.0
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
                    $fileInfoArr = $this->getFileArr($fileOrFiles, $replaceIfExist);
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
     * @return array An array that contains objects of type 'UploadedFile'.
     *
     * @throws FileException
     * @since 1.2.3
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
    private static function extractPathAndName($absPath): array {
        $DS = DIRECTORY_SEPARATOR;
        $cleanPath = str_replace('\\', $DS, str_replace('/', $DS, trim($absPath)));
        $pathArr = explode($DS, $cleanPath);

        if (count($pathArr) != 0) {
            $fPath = '';
            $name = $pathArr[count($pathArr) - 1];

            for ($x = 0 ; $x < count($pathArr) - 1 ; $x++) {
                $fPath .= $pathArr[$x].$DS;
            }

            return [
                'path' => $fPath,
                'name' => $name
            ];
        }

        return [
            'name' => $cleanPath,
            'path' => ''
        ];
    }
    private function getFileArr($fileOrFiles,$replaceIfExist, ?string $idx): array {
        $errIdx = 'error';
        $tempIdx = 'tmp_name';
        $fileInfoArr = [];
        $fileInfoArr[UploaderConst::NAME_INDEX] = $idx === null ? filter_var($fileOrFiles[UploaderConst::NAME_INDEX]) : filter_var($fileOrFiles[UploaderConst::NAME_INDEX][$idx]);
        $fileInfoArr[UploaderConst::SIZE_INDEX] = $idx === null ? filter_var($fileOrFiles[UploaderConst::SIZE_INDEX], FILTER_SANITIZE_NUMBER_INT) : filter_var($fileOrFiles[UploaderConst::SIZE_INDEX][$idx], FILTER_SANITIZE_NUMBER_INT);
        $fileInfoArr[UploaderConst::PATH_INDEX] = $this->getUploadDir();
        $fileInfoArr[UploaderConst::ERR_INDEX] = '';
        $nameSplit = explode('.', $fileInfoArr[UploaderConst::NAME_INDEX]);
        $fileInfoArr[UploaderConst::MIME_INDEX] = MIME::getType($nameSplit[count($nameSplit) - 1]);
        $fileInfoArr[UploaderConst::UPLOADED_INDEX] = false;
                
        $isErr = $idx === null ? $this->isError($fileOrFiles[$errIdx]) : $this->isError($fileOrFiles[$errIdx][$idx]);

        if (!$isErr) {
            if ($this->isValidExt($fileInfoArr[UploaderConst::NAME_INDEX])) {
                if (File::isDirectory($this->getUploadDir())) {
                    $filePath = $this->getUploadDir().'\\'.$fileInfoArr[UploaderConst::NAME_INDEX];
                    $filePath = str_replace('\\', '/', $filePath);
                    
                    //If in CLI, use copy (testing env)
                    $moveFunc = http_response_code() === false ? 'copy' : 'move_uploaded_file';
                        
                    if (!File::isFileExist($filePath)) {
                        $fileInfoArr[UploaderConst::EXIST_INDEX] = false;
                        $fileInfoArr[UploaderConst::REPLACE_INDEX] = false;
                        $name = $idx === null ? $fileOrFiles[$tempIdx] : $fileOrFiles[$tempIdx][$idx];
                        $sanitizedName = filter_var($name);
                        
                        
                        
                        if (!($moveFunc($sanitizedName, $filePath))) {
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
                            $sanitizedName = $sanitizedName = filter_var($name);

                            if ($moveFunc($sanitizedName, $filePath)) {
                                $fileInfoArr[UploaderConst::UPLOADED_INDEX] = true;
                            } else {
                                $fileInfoArr[UploaderConst::UPLOADED_INDEX] = false;
                            }
                        } else {
                            $fileInfoArr[UploaderConst::REPLACE_INDEX] = false;
                            $fileInfoArr[UploaderConst::ERR_INDEX] = UploaderConst::ALREADY_EXIST;
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

        return $fileInfoArr;
    }
    /**
     * Checks if PHP upload code is error or not.
     * 
     * @param int $code PHP upload code.
     * 
     * @return bool If the given code does not equal to UPLOAD_ERR_OK, the
     * method will return true.
     * 
     * @since 1.0
     */
    private function isError(int $code): bool {
        switch ($code) {
            case UPLOAD_ERR_OK:{
                $this->uploadStatusMessage = 'File Uploaded';

                return false;
            }
            case UPLOAD_ERR_INI_SIZE:{
                $this->uploadStatusMessage = 'File Size is Larger Than '.(intval(ini_get('upload_max_filesize')) / 1000).'KB. Found in php.ini.';
                break;
            }
            case UPLOAD_ERR_FORM_SIZE:{
                $this->uploadStatusMessage = 'File Size is Larger Than '.($this->getLimit() / 1000).'KB';
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
                $this->uploadStatusMessage = 'No File was Uploaded';
            }
        }

        return true;
    }
    /**
     * Checks if uploaded file is allowed or not.
     * 
     * @param string $fileName The name of the file (such as 'image.png')
     * 
     * @return bool If file extension is in the array of allowed types,
     * the method will return true.
     * 
     * @since 1.0
     */
    private function isValidExt(string $fileName) : bool {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        return in_array($ext, $this->getExts(),true) || in_array(strtolower($ext), $this->getExts(),true);
    }
}
