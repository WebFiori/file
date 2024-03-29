<?php
namespace webfiori\file;

use webfiori\json\Json;
/**
 * A class which is used by the class 'Uploader' to represents uploaded files.
 * 
 * The class is used by the class "FileUploader" to upload files as objects.
 * 
 * @author Ibrahim
 * 
 * @version 1.0
 */
class UploadedFile extends File {
    /**
     * A bool which is set to true in case the file is uploaded and was replaced.
     * 
     * @var bool
     * 
     * @since 1.0 
     */
    private $isReplace;
    /**
     * A bool which is set to true in case the file is uploaded without issues.
     * 
     * @var bool
     * 
     * @since 1.0 
     */
    private $isUploaded;
    /**
     * A string that contains a message which indicates what caused upload failure.
     * 
     * @var string 
     * 
     * @since 1.0
     */
    private $uploadError;
    /**
     * Creates new instance of the class.
     * 
     * This method will set the path and name to empty string. Also, it will 
     * set the size to 0 and ID to -1. Finally, it will set MIME type to 
     * "application/octet-stream"
     * 
     * @param string $fName The name of the file such as 'my-file.png'.
     * 
     * @param string $fPath The path of the file such as 'C:/Images/Test'.
     */
    public function __construct(string $fName = '', string $fPath = '') {
        parent::__construct($fName, $fPath);
        $this->setIsReplace(false);
        $this->setIsUploaded(false);
        $this->setUploadErr('');
    }
    /**
     * Returns a string that represents upload error.
     * 
     * @return string A string that can be used to identify the cause of upload 
     * failure. If no upload error, empty string is returned.
     * 
     * @since 1.0
     */
    public function getUploadError() : string {
        return $this->uploadError;
    }
    /**
     * Checks if the file was replaced by another uploaded file.
     * 
     * @return bool If the file was already exist in the server and a one
     * which has the same name was uploaded, the method will return true. Default 
     * return value is false.
     * 
     * @since 1.0
     */
    public function isReplace() : bool {
        return $this->isReplace;
    }
    /**
     * Checks if the file was uploaded successfully or not.
     * 
     * @return bool If the file was uploaded to the server without any errors,
     * the method will return true. Default return value is false.
     * 
     * @since 1.0
     */
    public function isUploaded() : bool {
        return $this->isUploaded;
    }
    /**
     * Sets the value of the property '$isReplace'.
     * The property is used to check if the file was already exist in the server and 
     * was replaced by another uploaded file. 
     * 
     * @param bool $bool A boolean. If true is passed, it means the file was replaced
     * by new one with the same name.
     * 
     * @since 1.0
     */
    public function setIsReplace(bool $bool) {
        $this->isReplace = $bool === true;
    }
    /**
     * Sets the value of the property '$isUploaded'.
     * The property is used to check if the file was successfully uploaded to the server.
     * 
     * @param bool $bool A boolean. If true is passed, it means the file was uploaded
     * without any errors.
     * 
     * @since 1.0
     */
    public function setIsUploaded(bool $bool) {
        $this->isUploaded = $bool === true;
    }
    /**
     * Sets an error message that indicates the cause of upload failure.
     * 
     * @param string $err Error message.
     * 
     * @since 1.0
     */
    public function setUploadErr(string $err) {
        $this->uploadError = $err;
    }
    /**
     * Returns a JSON string that represents the file.
     * 
     * @return Json An object of type 'Json' that contains file information. 
     * The object will have the following information:<br/>
     * <b>{<br/>&nbsp;&nbsp;"id":"",<br/>
     * &nbsp;&nbsp;"mime":"",<br/>
     * &nbsp;&nbsp;"name":"",<br/>
     * &nbsp;&nbsp;"path":"",<br/>
     * &nbsp;&nbsp;"sizeInBytes":"",<br/>
     * &nbsp;&nbsp;"sizeInKBytes":"",<br/>
     * &nbsp;&nbsp;"sizeInMBytes":"",<br/>&nbsp;&nbsp;"uploaded":"",<br/>
     * &nbsp;&nbsp;"isReplace":"",<br/>&nbsp;&nbsp;"uploadError":"",<br/>}</b>
     * 
     * @since 1.0
     */
    public function toJSON() : Json {
        $json = parent::toJSON();
        $json->addMultiple([
            'uploaded' => $this->isUploaded(),
            'isReplace' => $this->isReplace(),
            'uploadError' => $this->getUploadError()
        ]);

        return $json;
    }
}
