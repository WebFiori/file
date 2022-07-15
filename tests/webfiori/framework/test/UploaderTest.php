<?php

namespace webfiori\framework\test;

use webfiori\file\Uploader;
use PHPUnit\Framework\TestCase;
use webfiori\file\UploadFile;
use webfiori\json\Json;
use webfiori\file\File;
/**
 * Description of UploaderTest
 *
 * @author Ibrahim
 */
class UploaderTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $u = new Uploader();
        $this->assertEquals('files', $u->getAssociatedFileName());
        $this->assertEquals([], $u->getExts());
        $this->assertEquals('', $u->getUploadDir());
    }
    /**
     * @test
     */
    public function test01() {
        $u = new Uploader(__DIR__, [
            'sps', 'pdf', '.xop'
        ]);
        $u->setAssociatedFileName("\n ");
        $this->assertEquals('files', $u->getAssociatedFileName());
        $u->setAssociatedFileName('super-files ');
        $this->assertEquals('super-files', $u->getAssociatedFileName());
        $this->assertEquals([
            'sps', 'pdf', 'xop'
        ], $u->getExts());
        $this->assertEquals(str_replace('/', DS, str_replace('\\', DS, __DIR__)), $u->getUploadDir());
    }
    /**
     * @test
     */
    public function testAddExt00() {
        $u = new Uploader();
        $this->assertEquals([], $u->getExts());
        $this->assertTrue($u->addExt('.pdf'));
        $this->assertEquals([
            'pdf'
        ], $u->getExts());
        $this->assertFalse($u->addExt('.&sup'));
        $this->assertEquals([
            'pdf'
        ], $u->getExts());
        $this->assertTrue($u->addExt('xlsd'));
        $this->assertEquals([
            'pdf', 'xlsd'
        ], $u->getExts());
    }
    public function testUpload00() {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $u = new Uploader(__DIR__, [
            'txt'
        ]);
        $this->addTestFile('files', ROOT_DIR.'tests'.DS.'tmp'.DS.'testUpload.txt', true);
        $r = $u->upload();
        $this->assertEquals([
           [
               'name' => 'testUpload.txt',
               'size' => 51,
               'upload-path' => str_replace('/', DS, str_replace('\\', DS, __DIR__)),
               'upload-error' => 0,
               'mime' => 'text/plain',
               'is-exist' => false,
               'is-replace' => false,
               'uploaded' => false
           ] 
        ], $r);
        return  $u;
    }
    /**
     * @test
     * @depends testUpload00
     */
    public function testUpload01(Uploader $u) {
        $r = $u->getFiles(true);
        $file = $r[0];
        $this->assertTrue($file instanceof UploadFile);
        $this->assertEquals('testUpload.txt',$file->getName());
        $this->assertEquals('testUpload',$file->getNameWithNoExt());
        $this->assertFalse($file->isUploaded());
        $this->assertFalse($file->isReplace());
        $this->assertEquals('text/plain',$file->getMIME());
        $this->assertEquals(str_replace('/', DS, str_replace('\\', DS, __DIR__)),$file->getDir());
        $this->assertEquals(str_replace('/', DS, str_replace('\\', DS, __DIR__)).DS.'testUpload.txt',$file->getAbsolutePath());
        
        $this->assertEquals(0,$file->getUploadError());
        $this->assertEquals("{\"id\":-1,\"mime\":\"text\/plain\",\"name\":\"testUpload.txt\""
                . ",\"directory\":\"".Json::escapeJSONSpecialChars($file->getDir())."\",\"sizeInBytes\":0,"
                . "\"sizeInKBytes\":0,\"sizeInMBytes\":0,\"uploaded\":false,\"isReplace\":false,\"uploadError\":0}", $file.'');
    }
    /**
     * @test
     * @param Uploader $u
     */
    public function toJSONTest00() {
        $u = new Uploader(__DIR__);
        $this->assertEquals('{"uploadDirectory":"'.Json::escapeJSONSpecialChars($u->getUploadDir()).'",'
                . '"associatedFileName":"files","allowedTypes":[],"files":[]}', $u.'');
        $_SERVER['REQUEST_METHOD'] = 'post';
        $this->assertTrue($u->addExt('txt'));
        $this->assertFalse($u->addExt('   '));
        $this->addTestFile('files', ROOT_DIR.'tests'.DS.'tmp'.DS.'testUpload.txt', true);
        $this->addTestFile('files', ROOT_DIR.'tests'.DS.'tmp'.DS.'not-allowed.xp');
        $r = $u->uploadAsFileObj();
        
        $file1 = $r[0];
        $this->assertTrue($file1 instanceof UploadFile);
        $this->assertEquals('testUpload.txt',$file1->getName());
        $this->assertEquals('testUpload',$file1->getNameWithNoExt());
        $this->assertFalse($file1->isUploaded());
        $this->assertFalse($file1->isReplace());
        $this->assertEquals('text/plain',$file1->getMIME());
        $this->assertEquals(str_replace('/', DS, str_replace('\\', DS, __DIR__)),$file1->getDir());
        $this->assertEquals(str_replace('/', DS, str_replace('\\', DS, __DIR__)).DS.'testUpload.txt',$file1->getAbsolutePath());
        
        $this->assertEquals(0,$file1->getUploadError());
        
        $file2 = $r[1];
        $this->assertTrue($file2 instanceof UploadFile);
        $this->assertEquals('not-allowed.xp',$file2->getName());
        $this->assertEquals('not-allowed',$file2->getNameWithNoExt());
        $this->assertFalse($file2->isUploaded());
        $this->assertFalse($file2->isReplace());
        $this->assertEquals('application/octet-stream',$file2->getMIME());
        $this->assertEquals(str_replace('/', DS, str_replace('\\', DS, __DIR__)),$file2->getDir());
        $this->assertEquals(str_replace('/', DS, str_replace('\\', DS, __DIR__)).DS.'not-allowed.xp',$file2->getAbsolutePath());
        
        $this->assertEquals('not_allowed_type',$file2->getUploadError());
        
        $this->assertEquals('{'
                . '"uploadDirectory":"'.Json::escapeJSONSpecialChars($u->getUploadDir()).'",'
                . '"associatedFileName":'
                . '"files",'
                . '"allowedTypes":["txt"],'
                . '"files":[{"id":-1,'
                . '"mime":"text\/plain",'
                . '"name":"testUpload.txt",'
                . '"directory":"'.Json::escapeJSONSpecialChars($file1->getDir()).'",'
                . '"sizeInBytes":0,'
                . '"sizeInKBytes":0,'
                . '"sizeInMBytes":0,'
                . '"uploaded":false,'
                . '"isReplace":false,'
                . '"uploadError":0},'
                . '{"id":-1,"mime":'
                . '"application\/octet-stream",'
                . '"name":"not-allowed.xp",'
                . '"directory":"'.Json::escapeJSONSpecialChars($file2->getDir()).'",'
                . '"sizeInBytes":0,"sizeInKBytes":0,'
                . '"sizeInMBytes":0,'
                . '"uploaded":false,'
                . '"isReplace":false,'
                . '"uploadError":"not_allowed_type"}]}', $u.'');
    }
    /**
     * @test
     */
    public function testRemoveExt00() {
        $u = new Uploader(__DIR__, [
            'pdf', '.text', '.txt', 'jpg', 'png'
        ]);
        $this->assertEquals([
            'pdf', 'text', 'txt', 'jpg', 'png'
        ], $u->getExts());
        $this->assertTrue($u->removeExt('text'));
        $this->assertEquals([
            'pdf', 'txt', 'jpg', 'png'
        ], $u->getExts());
        $this->assertFalse($u->removeExt('text'));
        $this->assertEquals([
            'pdf', 'txt', 'jpg', 'png'
        ], $u->getExts());
        $this->assertTrue($u->removeExt('.pdf'));
        $this->assertEquals([
            'txt', 'jpg', 'png'
        ], $u->getExts());
    }
    /**
     * Adds a test file for testing upload functionality.
     * 
     * @param string $fileIdx
     * @param string $filePath
     * @param type $reset
     */
    public function addTestFile(string $fileIdx, string $filePath, $reset = false) {
        if ($reset) {
            $_FILES = [];
        }
        if (!isset($_FILES[$fileIdx])) {
            $_FILES[$fileIdx] = [];
            $_FILES[$fileIdx]['name'] = [];
            $_FILES[$fileIdx]['type'] = [];
            $_FILES[$fileIdx]['size'] = [];
            $_FILES[$fileIdx]['tmp_name'] = [];
            $_FILES[$fileIdx]['error']  = [];
        }
        
        $file = new File($filePath);
        $_FILES[$fileIdx]['name'][] = $file->getName();
        $_FILES[$fileIdx]['type'][] = $file->getMIME();
        $_FILES[$fileIdx]['size'][] = $file->getSize();
        $_FILES[$fileIdx]['tmp_name'][] = $file->getAbsolutePath();
        $_FILES[$fileIdx]['error'][] = 0;
    }
}
