<?php

namespace webfiori\framework\test;

use webfiori\file\Uploader;
use PHPUnit\Framework\TestCase;
use webfiori\file\UploadFile;
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
        $u->addTestFile('files', ROOT_DIR.'tests'.DS.'tmp'.DS.'testUpload.txt');
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
    }
}
