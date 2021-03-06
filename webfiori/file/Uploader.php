<?php
namespace webfiori\file;

use webfiori\json\Json;
use webfiori\json\JsonI;
use webfiori\file\MIME;
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
 * @version 1.2.3
 */
class Uploader implements JsonI {
    /**
     * The name of the index at which the file is stored in the array <b>$_FILES</b>.
     * @var string
     * @since 1.0
     */
    private $asscociatedName;
    /**
     * An array that contains all the allowed file types.
     * @var array An array of strings. 
     * @since 1.0
     */
    private $extentions = [];
    /**
     * An array which contains uploaded files.
     * @var array
     * @since 1.0 
     */
    private $files;
    /**
     * The directory at which the file (or files) will be uploaded to.
     * @var string A directory. 
     * @since 1.0
     */
    private $uploadDir;
    /**
     * Upload status message.
     * @var string
     * @since 1.0 
     */
    private $uploadStatusMessage;
    /**
     * Creates new instance of the class.
     * 
     * @param string $uploadPath A string that represents the location at 
     * which files will be uploaded to. Default value is 'app/sto/uploads'.
     * 
     * @param array $allowedTypes An array that contains allowed files types. The 
     * array can have values such as 'jpg', 'png', 'doc', etc...
     * 
     * @since 1.0
     */
    public function __construct(string $uploadPath = '', array $allowedTypes = []) {
        $this->uploadStatusMessage = 'NO ACTION';
        $this->files = [];
        $this->setAssociatedFileName('files');

        if (!$this->setUploadDir($uploadPath)) {
            $this->uploadDir = '';
        }
        $this->addExts($allowedTypes);
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
     * @return boolean If the extension is added, the method will return true.
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
     * of the addition. If added, it well be set to true.
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
     * <li><b>upload-error</b>: Any error which has happend during upload.</li>
     * <li><b>is-exist</b>: A boolean. Set to true if the file does exist in the server.</li>
     * <li><b>is-replace</b>: A boolean. Set to true if the file was already uploaded and replaced.</li>
     * <li><b>mime</b>: MIME type of the file.</li>
     * <li><b>uploaded</b>: A boolean. Set to true if the file was uploaded.</li>
     * </ul>
     * 
     * @param boolean $asObj If this parameter is set to true, the returned array 
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
                $retVal[] = $this->_createFileObjFromArray($fArr);
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
     * @return boolean If the extension was removed, the method will return true.
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
     * This method does not check whether the directory is exist or not. It 
     * just validate that the structure of the path is valid by replacing 
     * forward slashes with backward slashes. The directory will never update 
     * if the given string is empty.
     * 
     * @param string $dir Upload Directory (such as '/files/uploads' or 
     * 'C:/Server/uploads'). 
     * 
     * @return boolean If upload directory was updated, the method will 
     * return true. If not updated, the method will return false.
     * 
     * @since 1.0
     */
    public function setUploadDir(string $dir) {
        $retVal = false;
        $len = strlen($dir);

        if ($len > 0) {
            $fixedPath = File::fixPath($dir);

            if (strlen($fixedPath) > 0) {
                $dir = str_replace('/', '\\', $fixedPath);
                $this->uploadDir = !File::isDirectory($fixedPath) ? '\\'.$fixedPath : $fixedPath;
                $retVal = true;
            }
        }

        return $retVal;
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
     * @param bolean $replaceIfExist If a file with the given name found 
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
     * @since 1.0
     */
    public function upload(bool $replaceIfExist = false) : array {
        $this->files = [];
        $meth = getenv('REQUEST_METHOD');

        if ($meth === false) {
            $meth = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
        }
        $reqMeth = filter_var($meth, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strtoupper($reqMeth) == 'POST') {
            $fileOrFiles = null;
            $associatedInputName = filter_input(INPUT_POST, 'file');

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
                        $fileInfoArr = $this->_getFileArr($fileOrFiles, $replaceIfExist, $x);
                        $this->files[] = $fileInfoArr;
                    }
                } else {
                    //single file upload
                    $fileInfoArr = $this->_getFileArr($fileOrFiles, $replaceIfExist);
                    $this->files[] = $fileInfoArr;
                }
            }
        }

        return $this->files;
    }
    
    /**
     * Returns an array that contains objects of type 'UploadedFile'.
     * 
     * @param bolean $replaceIfExist If a file with the given name found 
     * and this parameter is set to true, the file will be replaced.
     * 
     * @return array An array that contains objects of type 'UploadedFile'.
     * 
     * @since 1.2.3
     */
    public function uploadAsFileObj(bool $replaceIfExist = false) : array {
        $uploadedFiles = $this->upload($replaceIfExist);
        $filesArr = [];

        foreach ($uploadedFiles as $fileArray) {
            $filesArr[] = $this->_createFileObjFromArray($fileArray);
        }

        return $filesArr;
    }
    private function _createFileObjFromArray($arr) {
        $file = new UploadFile(filter_var($arr['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS), $arr['upload-path']);
        $file->setMIME($arr['mime']);

        if (isset($arr['is-replace'])) {
            $file->setIsReplace($arr['is-replace']);
        }
        $file->setIsUploaded($arr['uploaded']);
        $file->setUploadErr($arr['upload-error']);

        return $file;
    }
    private function _getFileArr($fileOrFiles,$replaceIfExist, $idx = null) {
        
        $indices = [
            'name',//0
            'size',//1
            'upload-path',//2
            'upload-error',//3
            'is-exist',//4
            'is-replace',//5
            'mime',//6
            'uploaded'//7
        ];
        $errIdx = 'error';
        $tempIdx = 'tmp_name';
        $fileInfoArr = [];
        $fileInfoArr[$indices[0]] = $idx === null ? filter_var($fileOrFiles[$indices[0]], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : filter_var($fileOrFiles[$indices[0]][$idx], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $fileInfoArr[$indices[1]] = $idx === null ? filter_var($fileOrFiles[$indices[1]], FILTER_SANITIZE_NUMBER_INT) : filter_var($fileOrFiles[$indices[1]][$idx], FILTER_SANITIZE_NUMBER_INT);
        $fileInfoArr[$indices[2]] = $this->getUploadDir();
        $fileInfoArr[$indices[3]] = 0;
        $nameSplit = explode('.', $fileInfoArr[$indices[0]]);
        $fileInfoArr[$indices[6]] = MIME::getType($nameSplit[count($nameSplit) - 1]);

        $isErr = $idx === null ? $this->isError($fileOrFiles[$errIdx]) : $this->isError($fileOrFiles[$errIdx][$idx]);

        if (!$isErr) {
            if ($this->isValidExt($fileInfoArr[$indices[0]])) {
                if (File::isDirectory($this->getUploadDir())) {
                    $filePath = $this->getUploadDir().'\\'.$fileInfoArr[$indices[0]];
                    $filePath = str_replace('\\', '/', $filePath);

                    if (!File::isFileExist($filePath)) {
                        $fileInfoArr[$indices[4]] = false;
                        $fileInfoArr[$indices[5]] = false;
                        $name = $idx === null ? $fileOrFiles[$tempIdx] : $fileOrFiles[$tempIdx][$idx];
                        $sanitizedName = filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                        if (move_uploaded_file($sanitizedName, $filePath)) {
                            $fileInfoArr[$indices[7]] = true;
                        } else {
                            $fileInfoArr[$indices[7]] = false;
                        }
                    } else {
                        $fileInfoArr[$indices[4]] = true;

                        if ($replaceIfExist) {
                            $fileInfoArr[$indices[5]] = true;
                            unlink($filePath);
                            $name = $idx === null ? $fileOrFiles[$tempIdx] : $fileOrFiles[$tempIdx][$idx];
                            $sanitizedName = $sanitizedName = filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                            if (move_uploaded_file($sanitizedName, $filePath)) {
                                $fileInfoArr[$indices[7]] = true;
                            } else {
                                $fileInfoArr[$indices[7]] = false;
                            }
                        } else {
                            $fileInfoArr[$indices[5]] = false;
                            $fileInfoArr[$indices[7]] = false;
                        }
                    }
                } else {
                    $fileInfoArr[$indices[3]] = UploadErr::NO_SUCH_DIR;
                    $fileInfoArr[$indices[7]] = false;
                }
            } else {
                $fileInfoArr[$indices[7]] = false;
                $fileInfoArr[$indices[3]] = UploadErr::NOT_ALLOWED;
            }
        } else {
            $fileInfoArr[$indices[7]] = false;
            $fileInfoArr[$indices[3]] = $idx === null ? $fileOrFiles[$errIdx] : $fileOrFiles[$errIdx][$idx];
        }

        return $fileInfoArr;
    }
    /**
     * Checks if PHP upload code is error or not.
     * 
     * @param int $code PHP upload code.
     * 
     * @return boolean If the given code does not equal to UPLOAD_ERR_OK, the 
     * method will return true.
     * 
     * @since 1.0
     */
    private function isError($code) {
        switch ($code) {
            case UPLOAD_ERR_OK:{
                $this->uploadStatusMessage = 'File Uploaded';

                return false;
            }
            case UPLOAD_ERR_INI_SIZE:{
                $this->uploadStatusMessage = 'File Size is Larger Than '.(ini_get('upload_max_filesize') / 1000).'KB. Found in php.ini.';
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
     * @return boolean If file extension is in the array of allowed types, 
     * the method will return true.
     * 
     * @since 1.0
     */
    private function isValidExt($fileName) {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        return in_array($ext, $this->getExts(),true) || in_array(strtolower($ext), $this->getExts(),true);
    }
}
