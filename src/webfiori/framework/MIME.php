<?php
namespace webfiori\framework;

/**
 * A helper class with single method which is used to detect MIME of file
 * given its extension.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 */
class MIME {
    /**
     * An associative array that contains MIME types of common files.
     * 
     * The array contains the following MIME types:
     * <ul>
     * <li><b>Audio and Video Formats:</b>
     * <ul>
     * <li>avi: video/avi</li>
     * <li>3gp: video/3gpp</li>
     * <li>ogv: video/ogg</li>
     * <li>mp4: video/mp4</li>
     * <li>mov: video/quicktime</li>
     * <li>wmv: video/x-ms-wmv</li>
     * <li>flv: video/x-flv</li>
     * <li>mpeg: video/mpeg</li>
     * <li>midi: audio/midi</li>
     * <li>oga: audio/ogg</li>
     * <li>mp3: audio/mpeg</li>
     * <li>mid: audio/midi</li>
     * <li>wav: audio/aac</li>
     * <li>acc: audio/aac</li>
     * </ul></li>
     * <li><b>Image Formats:</b>
     * <ul>
     * <li>jpeg: image/jpeg</li>
     * <li>jpg: image/jpeg</li>
     * <li>png: image/png</li>
     * <li>bmp: image/bmp</li>
     * <li>ico: image/x-icon</li>
     * <li>tiff: image/tiff</li>
     * <li>svg: image/svg+xml</li>
     * <li>psd: image/vnd.adobe.photoshop</li>
     * <li>gif: image/gif</li>
     * </ul></li>
     * <li><b>Documents Formats:</b>
     * <ul>
     * <li>pdf: application/pdf</li>
     * <li>rtf: application/rtf</li>
     * <li>doc: application/msword</li>
     * <li>docx: application/vnd.openxmlformats-officedocument.wordprocessingml.document</li>
     * <li>ppt: application/vnd.ms-powerpoint</li>
     * <li>pptx: application/vnd.openxmlformats-officedocument.presentationml.presentation</li>
     * <li>xls: application/vnd.ms-excel</li>
     * <li>xlsx: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet</li>
     * </ul></li>
     * <li><b>Text Based Formats:</b>
     * <ul>
     * <li>txt: text/plain</li>
     * <li>php: text/plain</li>
     * <li>log: text/plain</li>
     * <li>ini: text/plain</li>
     * <li>css: text/css</li>
     * <li>js: application/javascript</li>
     * <li>asm: text/x-asm</li>
     * <li>java: text/x-java-source</li>
     * <li>htaccess: application/x-extension-htaccess</li>
     * <li>asp: text/asp</li>
     * <li>c: text/x-c</li>
     * <li>cpp: text/x-c</li>
     * <li>csv: text/csv</li>
     * <li>htm: text/html</li>
     * <li>html: text/html</li>
     * </ul></li>
     * <li><b>Other Formats:</b>
     * <ul>
     * <li>sql: application/sql</li>
     * <li>jar: application/java-archive</li>
     * <li>zip: application/zip</li>
     * <li>rar: application/x-rar-compressed</li>
     * <li>tar: application/x-tar</li>
     * <li>7z: application/x-7z-compressed</li>
     * <li>exe: application/vnd.microsoft.portable-executable</li>
     * <li>bin: application/octet-stream</li>
     * <li>woff: font/woff</li>
     * <li>woff2: font/woff2</li>
     * <li>otf: font/otf</li>
     * <li>ttf: font/ttf</li>
     * <li>ai: application/postscript</li>
     * <li>swf: application/x-shockwave-flash</li>
     * <li>ogx: application/ogg</li>
     * </ul>
     * 
     * @since 1.1.1
     */
    const TYPES = [
        //audio and video
        'avi' => 'video/avi',
        '3gp' => 'video/3gpp',
        'mp4' => 'video/mp4',
        'mov' => 'video/quicktime',
        'wmv' => 'video/x-ms-wmv',
        'flv' => 'video/x-flv',
        'ogv' => 'video/ogg',
        'mpeg' => 'video/mpeg',
        'midi' => 'audio/midi',
        'mid' => 'audio/midi',
        'acc' => 'audio/aac',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'oga' => 'audio/ogg',
        //images 
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
        'ico' => 'image/x-icon',
        'tiff' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'psd' => 'image/vnd.adobe.photoshop',
        'gif' => 'image/gif',
        //pdf 
        'pdf' => 'application/pdf',
        //rich text format
        'rtf' => 'application/rtf',
        //MS office documents
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        //other text based files
        'txt' => 'text/plain',
        'php' => 'text/plain',
        'log' => 'text/plain',
        'ini' => 'text/plain',
        'css' => 'text/css',
        'js' => 'text/javascript',
        'asm' => 'text/x-asm',
        'java' => 'text/x-java-source',
        'htaccess' => 'application/x-extension-htaccess',
        'asp' => 'text/asp',
        'c' => 'text/x-c',
        'cpp' => 'text/x-c',
        'csv' => 'text/csv',
        'htm' => 'text/html',
        'html' => 'text/html',
        //other files
        'sql' => 'application/sql',
        'jar' => 'application/java-archive',
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'tar' => 'application/x-tar',
        '7z' => 'application/x-7z-compressed',
        'exe' => 'application/vnd.microsoft.portable-executable',
        'bin' => 'application/octet-stream',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'otf' => 'font/otf',
        'ttf' => 'font/ttf',
        'ai' => 'application/postscript',
        'swf' => 'application/x-shockwave-flash',
        'ogx' => 'application/ogg'
    ];
    /**
     * Returns MIME type of a file given its extension.
     * 
     * The method will try to find MIME type based on its extension. The method 
     * will look for MIME in the constant MIME::YPES.
     * 
     * @param string $ext File extension without the suffix (such as 'jpg').
     * 
     * @return string If MIME type for the given extension is found, the method
     * will return it. Note that if no MIME type is found, the method will
     * return 'application/octet-stream' by default.
     * 
     */
    public static function getType(string $ext) : string {
        $lowerCase = strtolower($ext);
        $retVal = 'application/octet-stream';

        $types = self::TYPES;

        if (isset($types[$lowerCase])) {
            $retVal = self::TYPES[$lowerCase];
        }

        return $retVal;
    }
}
