<?php
namespace webfiori\file;

use webfiori\file\exceptions\FileException;
use webfiori\http\Response;
use webfiori\json\Json;
use webfiori\json\JsonI;
/**
 * A wrapper class around PHP's file handling functions which can be used to deal with files in simple way.
 * 
 * This class can be used to read and write files in binary. In addition to that, 
 * it can be used to view files in web browsers.
 * 
 * @author Ibrahim
 * 
 * @version 1.0
 */
class File implements JsonI {
    /**
     * The default MIME type of all files.
     * 
     */
    const DEFAULT_MIME = 'application/octet-stream';
    private static $errFunc;
    /**
     * The name of the attachment.
     * 
     * @var string 
     * 
     * @since 1.0
     */
    private $fileName;
    /**
     * The size of the file in bytes.
     * 
     * @var int
     * 
     * @since 1.0
     */
    private $fileSize;
    /**
     * A unique ID for the file.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $id;
    /**
     * MIME type of the attachment (such as 'image/png')
     * 
     * @var string 
     * 
     * @since 1.0
     */
    private $mimeType;
    /**
     * The full path to the file.
     * 
     * @var string 
     */
    private $path;
    /**
     * Raw data of the file in binary.
     * 
     * @var string
     * 
     * @since 1.0
     */
    private $rawData;
    /**
     * Creates new instance of the class.
     * 
     * This method will set the path and name to empty string. Also, it will 
     * set the size to 0 and ID to -1. Finally, it will set MIME type to 
     * "application/octet-stream"
     * 
     * @param string $fNameOrAbsPath The name of the file such as 'my-file.png'. 
     * This also can be the absolute path of the file (such as 'home/usr/ibrahim/my-file.png').
     * 
     * @param string $fPath The path of the file such as 'C:/Images/Test'. This can 
     * be ignored if absolute path of the file was provided for the first parameter.
     * 
     * @since 1.0
     */
    public function __construct(string $fNameOrAbsPath = '', string $fPath = '') {
        $this->mimeType = self::DEFAULT_MIME;
        $this->fileSize = -1;
        $this->path = '';
        $this->fileName = '';
        $this->rawData = '';
        self::initErrHandler();

        if (!$this->setPath($fPath)) {
            $info = $this->extractPathAndName($fNameOrAbsPath);
            $this->setDir($info['path']);
            $this->setName($info['name']);
        } else {
            $this->setName($fNameOrAbsPath);
        }

        if (self::isFileExist($this->getAbsolutePath())) {
            set_error_handler(self::$errFunc);
            $this->fileSize = filesize($this->getAbsolutePath());
            restore_error_handler();
        }
        $this->id = -1;
    }
    /**
     * Returns JSON string that represents basic file info.
     * 
     * @return string
     */
    public function __toString() {
        return $this->toJSON().'';
    }
    /**
     * Appends a string of data to the already existing data.
     * 
     * @param string|array $data A string that represents the extra data.
     * This also can be an array that holds multiple strings that will be
     * appended.
     * 
     * @since 1.1.9
     */
    public function append($data) {
        if (gettype($data) == 'array') {
            foreach ($data as $str) {
                $this->rawData .= $str;
            }
        } else {
            $this->rawData .= $data;
        }
    }
    /**
     * Attempt to create the file if it does not exist.
     * 
     * @param bool $createDirIfNotExist If this parameter is set to true and
     * chosen file path does not exist, the method will attempt to create the
     * directory before creating the file. Default is false.
     *
     * @throws FileException
     */
    public function create(bool $createDirIfNotExist = false) {
        $fPath = $this->getAbsolutePath();

        if (!$this->isExist()) {
            self::isDirectory($this->getDir(), $createDirIfNotExist);
            $resource = self::createResource('wb', $fPath);

            if (!is_resource($resource)) {
                throw new FileException('Unable to create a file at \''.$fPath.'\'.');
            } else {
                fwrite($resource, '');
                fclose($resource);
            }
        }
    }
    public static function fixPath($fPath) {
        $DS = DIRECTORY_SEPARATOR;
        $trimmedPath = str_replace('/', $DS, str_replace('\\', $DS, trim($fPath)));
        $len = strlen($trimmedPath);
        $start = '';

        if ($len != 0) {
            $start = $trimmedPath[0] == $DS ? $DS : '';

            while ($trimmedPath[$len - 1] == '/' || $trimmedPath[$len - 1] == '\\') {
                $tmpDir = trim($trimmedPath,'/');
                $trimmedPath = trim($tmpDir,'\\');
                $len = strlen($trimmedPath);
            }

            while ($trimmedPath[0] == '/' || $trimmedPath[0] == '\\') {
                $tmpDir = trim($trimmedPath,'/');
                $trimmedPath = trim($tmpDir,'\\');
            }
        }

        return $start.$trimmedPath;
    }
    /**
     * Returns the full path to the file.
     *
     * The full path of the file is a string that contains the path of the
     * file alongside its name. Assuming that the path is set to "C:/Users/Me/Documents"
     * and file name is set to "my-doc.docx", This method will return something like
     * "C:\Users\Me\Documents\my-do.docx".
     *
     * @return string Full path to the file (e.g. 'root\images\hello.png').
     * If the name of the file is not set or the path is not set, the method
     * will return empty string.
     *
     * @since 1.1.1
     */
    public function getAbsolutePath() : string {
        $fPath = $this->getDir();
        $name = $this->getName();

        if (strlen($fPath) != 0 && strlen($name) != 0) {
            return $fPath.DIRECTORY_SEPARATOR.$name;
        }

        return '';
    }
    /**
     * Returns an array that contains directories names of the calling script.
     * 
     * This method is used to extract the pathes of files at which they called
     * this method.
     * 
     * @return array An array that contains directories names of the calling files.
     */
    public static function getCallingFilesPaths() : array {
        $debugTrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 15);
        $retVal = [];

        foreach ($debugTrace as $traceEntry) {
            if (isset($traceEntry['file'])) {
                $file = $traceEntry['file'];
                $split = explode(DIRECTORY_SEPARATOR, $file);
                $withoutFile = array_diff($split, [$split[count($split) - 1]]);
                $dir = implode(DIRECTORY_SEPARATOR, $withoutFile);

                if (strlen($dir) != 0) {
                    $dir .= DIRECTORY_SEPARATOR;

                    if (!in_array($dir, $retVal)) {
                        $retVal[] = $dir;
                    }
                }
            }
        }

        return $retVal;
    }
    /**
     * Split file raw data into chunks of fixed size.
     *
     * @param int $chunkSize The number of bytes in every chunk. If a negative
     * number is given, default value is used which is 50.
     *
     * @param bool $encode If this parameter is set to true, the returned
     * chunks of data will be encoded using base 64 encoding. Default is
     * true.
     *
     * @return array The method will return an array that holds file data as
     * chunks.
     *
     * @since 1.2.1
     */
    public function getChunks(int $chunkSize = 50, bool $encode = true) : array {
        if ($chunkSize < 0) {
            $chunkSize = 50;
        }

        $data = $this->getRawData($encode);

        $dataLen = strlen($data);
        $retVal = [];
        $index = 0;

        while ($index < $dataLen) {
            $retVal[] = substr($data, $index, $chunkSize);
            $index += $chunkSize;
        }

        $remainingChars = $dataLen - count($retVal) * $chunkSize;

        if ($remainingChars > 0) {
            $retVal[] = substr($data, $index);
        }

        return $retVal;
    }
    /**
     * Returns the directory at which the file exist on.
     *
     * The directory is simply the folder that contains the file. For example,
     * the directory can be something like "C:\Users\Me\Documents". Note that the
     * returned directory will be using backward slashes "\".
     *
     * @return string The directory at which the file exist on.
     *
     * @since 1.0
     */
    public function getDir() : string {
        return $this->path;
    }
    /**
     * Extract file extension from file name and return it.
     *
     * File extension will depend on one of two things, file name and MIME.
     * If file name contains extension such as .xyz, then 'xyz' will be the returned
     * value. If file name is not set, the method will return 'bin'.
     *
     * @return string A string such as 'mp3' or 'jpeg'. Default return value is
     * 'bin' which stands for binary file.
     *
     * @since 1.2.0
     */
    public function getExtension() : string {
        $fArr = explode('.', $this->getName());

        if (count($fArr) > 1) {
            return $fArr[count($fArr) - 1];
        }

        $mime = $this->getMIME();
        $mimeTypes = MIME::TYPES;

        foreach ($mimeTypes as $ext => $xMime) {
            if ($xMime == $mime) {
                return $ext;
            }
        }

        return 'bin';
    }
    /**
     * Returns the ID of the file.
     *
     * This method is helpful in case the file is stored in database.
     *
     * @return string The ID of the file. If the ID is not set, the method
     * will return -1.
     *
     * @since 1.0
     */
    public function getID() {
        return $this->id;
    }
    /**
     * Returns the time at which the file was last modified.
     *
     * Note that this method will work only if the file exist in the file system.
     *
     * @param string|null $format An optional format. The supported formats are the
     * same formats which are supported by the function <code>date()</code>.
     *
     * @return string|int If no format is provided, the method will return the
     * time as integer. If a format is given, the method will return the time as
     * specified by the format. If the file does not exist, the method will return
     * 0.
     *
     * @since 1.1.7
     */
    public function getLastModified(?string $format = 'Y-m-d H:i:s') {
        if ($this->isExist()) {
            clearstatcache();

            if ($format !== null) {
                return date($format, filemtime($this->getAbsolutePath()));
            }

            return filemtime($this->getAbsolutePath());
        }

        return 0;
    }
    /**
     * Returns MIME type of the file.
     *
     * Note that if the file is specified by its path and name, the method
     * File::read() must be called before calling this method to update its
     * MIME type.
     *
     * @return string MIME type of the file. If MIME type of the file is not set
     * or not detected, the method will return 'application/octet-stream'.
     *
     * @since 1.0
     */
    public function getMIME() : string {
        return $this->mimeType;
    }

    /**
     * Returns the name of the file.
     *
     * The name is used to construct the absolute path of the file in addition
     * to its path.
     *
     * @return string The name of the file. If the name is not set, the method
     * will return empty string.
     *
     * @since 1.0
     */
    public function getName() : string {
        return $this->fileName;
    }
    /**
     * Return file name without its extension.
     *
     * @return string File name without its extension.
     */
    public function getNameWithNoExt() : string {
        $currentName = $this->getName();
        $expl = explode('.', $currentName);

        if (count($expl) > 1) {
            array_pop($expl);
        }

        return implode('.', $expl);
    }

    /**
     * Returns the raw data of the file.
     *
     * The raw data is simply a string. It can be binary string or any basic
     * string.
     *
     * @param bool $encode If this parameter is set to true, the returned
     * string will be base 64 encoded. Encoding is performed to make sure that
     * the file does not get corrupted when it's transferred on the web.
     *
     * @return string Raw data of the file. If no data is set, the method
     * will return empty string.
     *
     * @since 1.0
     */
    public function getRawData(bool $encode = false) : string {
        $retVal = $this->rawData;

        if ($encode === true) {
            $retVal = $this->getRawDataEncoded();
        }

        return $retVal;
    }
    /**
     * Returns the raw data of the file encoded using base 64 encoding.
     *
     * @return string Raw data of the file encoded using base 64 encoding.
     *
     *
     */
    public function getRawDataEncoded() : string {
        return base64_encode($this->rawData);
    }
    /**
     * Returns the size of the file in bytes.
     *
     * @return int Size of the file in bytes. If the raw data of the file
     * is not set or the file does not exist, the method will return -1.
     */
    public function getSize() : int {
        return $this->fileSize;
    }
    /**
     * Checks if a given directory exists or not.
     *
     * @param string $dir A string in a form of directory (Such as 'root/home/res').
     *
     * @param bool $createIfNot If set to true and the given directory does
     * not exists, The method will try to create the directory.
     *
     * @return bool In general, the method will return false if the
     * given directory does not exist. The method will return true only
     * in two cases, If the directory exits, or it does not exist but was created.
     *
     * @since 1.0
     */
    public static function isDirectory(string $dir, bool $createIfNot = false) : bool {
        $dirFix = str_replace('\\', '/', $dir);

        if (!is_dir($dirFix)) {
            if ($createIfNot === true && mkdir($dir, 0777 , true)) {
                return true;
            }

            return false;
        }

        return true;
    }
    /**
     * Checks if the file exist or not.
     *
     * @return bool If the file exist, the method will return true. Other than
     * that, the method will return false.
     *
     * @since 1.1.6
     */
    public function isExist() : bool {
        return self::isFileExist($this->getAbsolutePath());
    }
    /**
     * Checks if file exist or not without throwing errors.
     *
     * This method uses the function 'file_exists()' to check if a file is exist
     * or not given its path. The only difference is that it will not
     * throw an error if path is invalid.
     *
     * @param string $path File path.
     *
     * @since 1.1.8
     */
    public static function isFileExist(string $path) : bool {
        self::initErrHandler();
        set_error_handler(self::$errFunc);
        $isExist = file_exists($path);
        restore_error_handler();

        return $isExist;
    }
    /**
     * Reads the file in binary mode.
     *
     * First of all, this method checks the existence of the file. If it
     * is existed, it tries to open the file in binary mode 'rb'. If a resource
     * is created, it is used to read the content of the file. Also, the method
     * will try to set MIME type of the file. If MIME type was not detected,
     * it will set to 'application/octet-stream'. If the method is unable to
     * read the file, it will throw an exception.
     *
     * @param int $from The byte at which the method will start reading from. If -1
     * is given, then the method will start reading from byte 0.
     *
     * @param int $to The byte at which the method will read data to. If -1
     * is given, then the method will read till last byte. Default is
     * -1.
     *
     * @throws FileException The method will throw an exception in 3 cases:
     * <ul>
     * <li>If file name is not set.</li>
     * <li>If file path is not set.</li>
     * <li>If the file does not exist.</li>
     * </ul>
     */
    public function read(int $from = -1, int $to = -1) {
        $fPath = $this->checkNameAndPath();

        if (!$this->readHelper($fPath,$from,$to)) {
            throw new FileException('File not found: \''.$fPath.'\'.');
        }
    }

    /**
     * Reads a file and decode its content from base 64.
     *
     * @throws FileException
     */
    public function readDecoded() {
        $this->read();
        $raw = $this->getRawData();
        $this->setRawData($raw, true);
    }
    /**
     * Removes a file given its name and path.
     *
     * Before calling this method, the name of the file and its path must
     * be specified.
     *
     * @return bool If the file was removed, the method will return
     * true. Other than that, the method will return false.
     *
     * @since 1.1.2
     */
    public function remove() : bool {
        if ($this->isExist()) {
            $this->rawData = '';
            unlink($this->getAbsolutePath());

            return true;
        }

        return false;
    }
    /**
     * Sets the name of the directory at which the file exist on.
     *
     * The directory is simply the folder that contains the file. For example,
     * the directory can be something like "C:/Users/Me/Documents". The directory can
     * use forward slashes or backward slashes.
     *
     * @param string $dir The directory which will contain the file. It must
     * be non-empty string in order to set.
     *
     * @return bool The method will return true if the directory is set. Other
     * than that, the method will return false.
     *
     * @since 1.0
     */
    public function setDir(string $dir): bool {
        return $this->setPath($dir);
    }
    /**
     * Sets the ID of the file.
     *
     * This method is helpful in case the file is stored in database.
     *
     * @param string $id The unique ID of the file.
     *
     * @since 1.0
     */
    public function setId(string $id) {
        $this->id = $id;
    }
    /**
     * Sets the MIME type of the file.
     *
     * It is not recommended to update MIME type of the file manually. Only
     * use this method for custom file types. MIME type will be set only
     * if its non-empty string.
     *
     * @param string $type MIME type (such as 'application/pdf')
     *
     * @since 1.0
     */
    public function setMIME(string $type) {
        if (strlen($type) != 0) {
            $this->mimeType = $type;
        }
    }
    /**
     * Sets the name of the file (such as 'my-image.png')
     *
     * The name is used to construct the absolute path of the file in addition
     * to its path. The name of the file must include its extension (or suffix).
     *
     * @param string $name The name of the file.
     *
     * @since 1.0
     */
    public function setName(string $name) {
        $trimmed = trim($name);

        if (strlen($trimmed) != 0) {
            $this->fileName = $name;
            $this->extractMimeFromName();
        }
    }

    /**
     * Sets the binary representation of the file.
     *
     * The raw data is simply a string. It can be binary string or any basic
     * string. Also, it can be a blob which was retrieved from a database.
     *
     * @param string $raw Binary raw data of the file.
     *
     * @param bool $decode If set to true, the method will assume that given
     * data is encoded and the method will attempt to decode provided data.
     *
     * @param bool $strict If set to true, the method will only decode data
     * within base64 alphabet. If any character is found to be outside base 64
     * alphabet, it will throw an exception. Otherwise invalid characters will
     * be silently discarded.
     *
     * @throws FileException
     *
     * @since 1.0
     */
    public function setRawData(string $raw, bool $decode = false, bool $strict = false) {
        if (strlen($raw) > 0) {
            if ($decode === true) {
                $this->setRawDataDecoded($raw, $strict);
            } else {
                $this->rawData = $raw;
                $this->setSize(strlen($raw));
            }
        }
    }

    /**
     * Sets the binary representation of the file.
     *
     * This method will decode the given data using base 64 decoding before setting
     * the data.
     *
     * @param string $raw The raw data of the file.
     *
     * @param bool $strict If set to true, the method will only decode data
     * within base64 alphabet. If any character is found to be outside base 64
     * alphabet, it will throw an exception. Otherwise invalid characters will
     * be silently discarded.
     *
     * @throws FileException
     */
    public function setRawDataDecoded(string $raw, bool $strict = false) {
        $decoded = base64_decode($raw, $strict);

        if ($decoded === false) {
            throw new FileException('Base 64 decoding failed due to characters outside base 64 alphabet.');
        }
        $this->rawData = $decoded;
        $this->setSize(strlen($this->rawData));
    }

    /**
     * Returns an array that contains integer values which represents file data as binary.
     *
     * Each index of the array will have an integer value between 0 and 255 inclusive.
     *
     * @return array An array that contains integer values which represents file data as binary.
     */
    public function toBytesArray() : array {
        $raw = $this->getRawData();
        $asArray = unpack('C*', $raw);

        return array_values($asArray);
    }

    /**
     * Returns an array of strings which represents file data as hex.
     *
     * Each index will have a string of two characters which represents each byte.
     *
     * @return array An array of strings which represents file data as hex.
     */
    public function toHexArray() : array {
        $bytes = $this->toBytesArray();
        $hexArr = [];

        foreach ($bytes as $byte) {
            $hexArr[] = sprintf("%02X", $byte);
        }

        return  $hexArr;
    }
    /**
     * Returns a JSON string that represents the file.
     *
     * @return Json An object of type 'Json' that contains file information.
     * The object will have the following information:<br/>
     * <b>{<br/>&nbsp;&nbsp;"id":"",<br/>&nbsp;&nbsp;"mime":"",<br/>&nbsp;&nbsp;"name":"",<br/>
     * &nbsp;&nbsp;"path":"",<br/>&nbsp;&nbsp;"sizeInBytes":"",<br/>&nbsp;&nbsp;"sizeInKBytes":"",<br/>
     * &nbsp;&nbsp;"sizeInMBytes":""<br/>}</b>
     *
     * @since 1.0
     */
    public function toJSON() : Json {
        $size = $this->getSize() != -1 ? $this->getSize() : 0;

        return new Json([
            'id' => $this->getID(),
            'mime' => $this->getMIME(),
            'name' => $this->getName(),
            'directory' => $this->getDir(),
            'sizeInBytes' => $size,
            'sizeInKBytes' => $size / 1024,
            'sizeInMBytes' => ($size / 1024) / 1024
        ]);
    }
    /**
     * Display the file.
     *
     * If the raw data of the file is null, the method will
     * try to read the file that was specified by the name and its path. If
     * the method is unable to read the file, an exception is thrown.
     *
     * @param bool $asAttachment If this parameter is set to
     * true, the header 'content-disposition' will have the attribute 'attachment'
     * set instead of 'inline'. This will trigger 'save as' dialog to appear.
     *
     * @throws FileException An exception with the message "MIME type of raw data is not set."
     * If MIME type of the file is not set.
     *
     * @since 1.1.1
     */
    public function view(bool $asAttachment = false) {
        $raw = $this->getRawData();

        if (strlen($raw) == 0) {
            $this->read();
        }
        $this->viewFileHelper($asAttachment);
    }
    /**
     * Write raw binary data into a file.
     *
     * The method will write the data using the binary write mode.
     * If it fails, It will throw an exception.
     *
     * @param bool $append If this attribute is set to true, the new raw
     * data will be appended to the file instead of overriding it. Default is true.
     *
     * @param bool $createIfNotExist If the file was not in filesystem and this
     * parameter is set to true, the method will attempt to create the file
     * alongside the folder that will contain the file. Default is false.
     *
     * @throws FileException The method will throw an exception in 3 cases:
     * <ul>
     * <li>If file name is not set.</li>
     * <li>If file path is not set.</li>
     * <li>If the file does not exist and the parameter $create is set to false.</li>
     * </ul>
     *
     * @since 1.1.1
     */
    public function write(bool $append = true, bool $createIfNotExist = false) {
        $pathV = $this->checkNameAndPath();

        if ($createIfNotExist) {
            $this->create(true);
        }
        $this->writeHelper($pathV, $append === true);
    }

    /**
     * Encode file data using base 64 and store it in binary file with the
     * extension .bin
     *
     * The final file name will consist of file name + original extension + 'bin'.
     * For example, if file name is 'hello.txt', the output file will be
     * 'hello.txt.bin'.
     *
     * @throws FileException
     */
    public function writeEncoded() {
        $currentName = $this->getName();
        $this->setName($currentName.'.bin');
        $this->create(true);
        $pathV = $this->checkNameAndPath();
        $this->writeHelper($pathV, false, true);
        $this->setName($currentName);
    }

    /**
     *
     * @return string
     *
     * @throws FileException
     */
    private function checkNameAndPath(): string {
        clearstatcache();
        $fName = $this->getName();

        if (strlen($fName) != 0) {
            $fPath = $this->getDir();

            if (strlen($fPath) == 0) {
                $possiblePaths = self::getCallingFilesPaths();

                foreach ($possiblePaths as $fPath) {
                    if (self::isFileExist($fPath.DIRECTORY_SEPARATOR.$fName)) {
                        $this->setDir($fPath);

                        return $this->getAbsolutePath();
                    }
                }
            } else {
                return $this->getAbsolutePath();
            }
            throw new FileException('Path cannot be empty string.');
        }
        throw new FileException('File name cannot be empty string.');
    }
    /**
     * 
     * @param string $mode
     * 
     * @param string $path
     * 
     * @return bool|resource
     */
    public static function createResource(string $mode, string $path) {
        set_error_handler(self::$errFunc);
        $resource = fopen($path, $mode);
        restore_error_handler();

        if (is_resource($resource)) {
            return $resource;
        }

        return false;
    }

    private function doNotUseClassResponse($contentType, $asAttachment) {
        $headersArr = [
            'Accept-Ranges: bytes',
            'content-type: '.$contentType
        ];

        if (isset($_SERVER['HTTP_RANGE'])) {
            $expl = $this->readRange();
            http_response_code(206);
            $headersArr[] = 'content-range: '.'bytes '.$expl[0].'-'.$expl[1].'/'.$this->getSize();
            $headersArr[] = 'content-length: '.($expl[1] - $expl[0]);
        } else {
            $headersArr[] = 'Content-Length: '.$this->getSize();
        }

        if ($asAttachment === true) {
            $headersArr[] = 'Content-Disposition: attachment; filename="'.$this->getName().'"';
        } else {
            $headersArr[] = 'Content-Disposition: inline; filename="'.$this->getName().'"';
        }

        foreach ($headersArr as $h) {
            //Check if in PHP Unit or not. If in 
            if (!defined('TESTING') || TESTING === false) {
                header($h);
            }
        }
        echo $this->getRawData();
        die();
    }
    private function extractMimeFromName() {
        $exp = explode('.', $this->getName());

        if (count($exp) > 1) {
            $ext = $exp[count($exp) - 1];
            $this->setMIME(MIME::getType($ext));
        }
    }
    private function extractPathAndName($absPath): array {
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
    private static function initErrHandler() {
        self::$errFunc = function (int $errno, string $errstr, string $errfile, int $errline)
        {
            throw new FileException($errstr.' At class File line '.$errline, $errno);
        };
    }
    private function readHelper($fPath,$from,$to) {
        if ($this->isExist()) {
            $fSize = filesize($fPath);
            $this->setSize($fSize);
            $bytesToRead = $to - $from > 0 ? $to - $from : $this->getSize();
            $resource = self::createResource('rb', $fPath);

            if ($bytesToRead > $this->getSize() || $to > $this->getSize()) {
                throw new FileException('Reached end of file while trying to read '.$bytesToRead.' byte(s).');
            }

            if (is_resource($resource)) {
                if ($bytesToRead > 0) {
                    fseek($resource, $from);
                }

                if ($bytesToRead > 0) {
                    $this->rawData = fread($resource, $bytesToRead);
                }
                fclose($resource);
                $ext = pathinfo($this->getName(), PATHINFO_EXTENSION);
                $this->setMIME(MIME::getType($ext));

                return true;
            }
            throw new FileException('Unable to open the file \''.$fPath.'\'.');
        } else {
            throw new FileException('File not found: \''.$fPath.'\'.');
        }
    }
    private function readRange() {
        $range = filter_var($_SERVER['HTTP_RANGE']);
        $rangeArr = explode('=', $range);
        $expl = explode('-', $rangeArr[1]);

        if (strlen($expl[1]) == 0) {
            $expl[1] = $this->getSize();
        }
        $this->read($expl[0], $expl[1]);

        return $expl;
    }
    /**
     * Sets the path of the file.
     *
     * The path is simply the folder that contains the file. For example,
     * the path can be something like "C:/Users/Me/Documents". The path can
     * use forward slashes or backward slashes.
     *
     * @param string $fPath The folder which will contain the file. It must
     * be non-empty string in order to set.
     *
     * @return bool The method will return true if the path is set. Other
     * than that, the method will return false.
     * 
     * @since 1.0
     * 
     * @deprecated since version 1.1.5 Use File::setDir() instead.
     */
    private function setPath(string $fPath) {
        $retVal = false;
        $pathV = self::fixPath($fPath);
        $len = strlen($pathV);

        if ($len > 0) {
            $this->path = $pathV;
            $retVal = true;
        }

        return $retVal;
    }
    private function setSize($size) {
        if ($size >= 0) {
            $this->fileSize = $size;
        }
    }

    private function useClassResponse($contentType, $asAttachment) {
        Response::addHeader('Accept-Ranges', 'bytes');
        Response::addHeader('content-type', $contentType);

        if (isset($_SERVER['HTTP_RANGE'])) {
            $expl = $this->readRange();
            Response::setCode(206);
            Response::addHeader('content-range', 'bytes '.$expl[0].'-'.$expl[1].'/'.$this->getSize());
            Response::addHeader('content-length', $expl[1] - $expl[0]);
        } else {
            Response::addHeader('Content-Length', $this->getSize());
        }

        if ($asAttachment === true) {
            Response::addHeader('Content-Disposition', 'attachment; filename="'.$this->getName().'"');
        } else {
            Response::addHeader('Content-Disposition', 'inline; filename="'.$this->getName().'"');
        }
        Response::write($this->getRawData());

        if (!defined('__PHPUNIT_PHAR__')) {
            Response::send();
        }
    }
    private function viewFileHelper($asAttachment) {
        $contentType = $this->getMIME();

        if (class_exists('\webfiori\http\Response')) {
            $this->useClassResponse($contentType, $asAttachment);
        } else {
            $this->doNotUseClassResponse($contentType, $asAttachment);
        }
    }

    /**
     *
     * @param string $fPath
     * @param bool $append
     * @param bool $encode
     * @return bool
     * @throws FileException
     */
    private function writeHelper(string $fPath, bool $append, bool $encode = false) {
        if (strlen($this->getRawData()) == 0) {
            throw new FileException("No data is set to write.");
        }

        if (!$this->isExist()) {
            throw new FileException("File not found: '$fPath'.");
        } else {
            if ($append) {
                $resource = self::createResource('ab', $fPath);
            } else {
                $resource = self::createResource('rb+', $fPath);
            }
        }

        if (!is_resource($resource)) {
            throw new FileException('Unable to open the file at \''.$fPath.'\'.');
        } else {
            fwrite($resource, $this->getRawData($encode));
            fclose($resource);

            return true;
        }
    }
}
