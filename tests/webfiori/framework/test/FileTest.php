<?php
namespace webfiori\framework\test;

use PHPUnit\Framework\TestCase;
use webfiori\file\File;
use webfiori\file\exceptions\FileException;
use webfiori\http\Response;
use webfiori\json\Json;
/**
 * A test class for testing the class 'webfiori\framework\File'.
 *
 * @author Ibrahim
 */
class FileTest extends TestCase {
    /**
     * @test
     */
    public function testEncodeDecode00() {
        $file = new File();
        $file->setRawData('Super');
        $this->assertEquals('Super', $file->getRawData());
        $this->assertEquals(base64_encode('Super'), $file->getRawData(true));
    }
    /**
     * @test
     */
    public function testEncodeDecode01() {
        $file = new File();
        $file->setRawData(base64_encode('Super'), true);
        $this->assertEquals('Super', $file->getRawData());
        $this->assertEquals(base64_encode('Super'), $file->getRawData('e'));
    }
    /**
     * @test
     */
    public function testEncodeDecode02() {
        $file = new File();
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Base 64 decoding failed due to characters outside base 64 alphabet.');
        $file->setRawData(base64_encode('Super').'X', true, true);
        $this->assertEquals('Super', $file->getRawData());
        $this->assertEquals(base64_encode('Super'), $file->getRawData('e'));
    }
    /**
     * @test
     */
    public function test00() {
        $file = new File();
        $this->assertEquals('bin', $file->getExtension());
        $this->assertEquals('',$file->getName());
        $this->assertEquals('',$file->getDir());
        $this->assertEquals(-1,$file->getID());
        $this->assertEquals('', $file->getRawData());
        $this->assertEquals('application/octet-stream',$file->getMIME());
        $file->setId(100);
        $this->assertEquals(100, $file->getID());
        return $file;
    }
    /**
     * @test
     */
    public function test01() {
        $file = new File('text-file.txt',ROOT_PATH.DS.'tests'.DS.'files');
        $this->assertEquals('text-file.txt',$file->getName());
        $this->assertEquals(ROOT_PATH.DS.'tests'.DS.'files',$file->getDir());
        $this->assertEquals(-1,$file->getID());
        $this->assertEquals('', $file->getRawData());
        $this->assertEquals('text/plain',$file->getMIME());

        return $file;
    }
    /**
     * @test
     */
    public function test02() {
        $file = new File();
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('File name cannot be empty string.');
        $file->read();
    }
    /**
     * @test
     */
    public function test03() {
        $file = new File('hello.txt');
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Path cannot be empty string.');
        $file->read();
    }
    /**
     * @test
     */
    public function test04() {
        $file = new File('hello.txt', ROOT_PATH);
        $this->expectException(FileException::class);
        $this->expectExceptionMessage("File not found: '".$file->getAbsolutePath()."'");
        $file->read();
    }
    /**
     * @test
     */
    public function test05() {
        $file = new File();
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('File name cannot be empty string.');
        $file->write();
    }
    /**
     * @test
     */
    public function test06() {
        $file = new File('hello.txt');
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Path cannot be empty string.');
        $file->write();
    }
    /**
     * @test
     */
    public function test07() {
        $file = new File('hello.txt', ROOT_PATH);
        $this->expectException(FileException::class);
        $this->expectExceptionMessage("No data is set to write.");
        $file->write(false, true);
    }
    /**
     * @test
     */
    public function test08() {
        $file = new File('in-dir.txt');
        $file->read(0, 1);
        $this->assertEquals('T', $file->getRawData());
        $this->assertEquals([84], $file->toBytesArray());
        $this->assertEquals(['54'], $file->toHexArray());
        $file->read();
        $this->assertEquals("This is to test if read from same directory is working.\n", $file->getRawData());
        $this->assertEquals('{"id":-1,"mime":"text\/plain","name":"in-dir.txt","directory":"C:\\inetpub\\wwwroot\\dev\\IbrahimSpace\\file\\tests\\webfiori\\framework\\test","sizeInBytes":57,"sizeInKBytes":0.0556640625,"sizeInMBytes":5.4359436035156E-5}', $file.'');
    }
    /**
     * @test
     */
    public function testReadChunk01() {
        $file = new File('text-file-3.txt',ROOT_PATH.DS.'tests'.DS.'files');
        $file->read();
        $data = $file->getChunks(-100, false);
        $this->assertEquals([
            "Testing the class 'File'. Hello. Good Bad Random\nT",
            "esting the class 'File'. Hello. Good Bad Random\nTe",
            "sting the class 'File'. Hello. Good Bad Random\nTes",
            "ting the class 'File'. Hello. Good Bad Random\nTest",
            "ing the class 'File'. Hello. Good Bad Random\nTesti",
            "ng the class 'Fi"
        ], $data);
    }

    /**
     * @test
     */
    public function testBytesArray00() {
        $file = new File();
        $this->assertEquals([], $file->toBytesArray());
        $file->setRawData('a');
        $this->assertEquals([97], $file->toBytesArray());
        $file->setRawData('aAbcD');
        $this->assertEquals([97, 65, 98, 99, 68], $file->toBytesArray());
        $this->assertEquals(['61', '41', '62', '63', '44'], $file->toHexArray());
        $this->assertEquals('aAbcD', hex2bin(implode($file->toHexArray())));
        $file->setRawData('مرحبا بالعالم');
        $this->assertEquals('مرحبا بالعالم', hex2bin(implode($file->toHexArray())));
    }
    /**
     * @test
     */
    public function testGetExtension00() {
        $file = new File();
        $this->assertEquals('bin', $file->getExtension());
        $this->assertEquals('', $file->getNameWithNoExt());
    }
    /**
     * @test
     */
    public function testGetExtension01() {
        $file = new File('good/file');
        $this->assertEquals('bin', $file->getExtension());
        $this->assertEquals('file', $file->getNameWithNoExt());
    }
    /**
     * @test
     */
    public function testGetExtension02() {
        $file = new File('good/file.mp3');
        $this->assertEquals('mp3', $file->getExtension());
        $this->assertEquals('file', $file->getNameWithNoExt());
    }
    /**
     * @test
     */
    public function testGetExtension03() {
        $file = new File('good/file.xyz');
        $this->assertEquals('xyz', $file->getExtension());
        $this->assertEquals('file', $file->getNameWithNoExt());
    }
    /**
     * @test
     */
    public function testGetExtension04() {
        $file = new File('good/file.xyz.super');
        $this->assertEquals('super', $file->getExtension());
        $this->assertEquals('file.xyz', $file->getNameWithNoExt());
    }
    /**
     * @test
     */
    public function testReadChunk02() {
        $file = new File();
        $data = $file->getChunks();
        $this->assertEquals([
        ], $data);
    }
    /**
     * @test
     */
    public function testRead00() {
        $file = new File('not-exist.txt', ROOT_PATH);
        $this->expectException(FileException::class);
        $this->expectExceptionMessage("File not found: '".$file->getAbsolutePath()."'");
        $file->read();
    }
    /**
     * @test
     */
    public function testReadChunk00() {
        $file = new File('text-file.txt',ROOT_PATH.DS.'tests'.DS.'files');
        $file->read();
        $data = $file->getChunks(3, true);
        $this->assertEquals(base64_encode('Testing the class \'File\'.'), implode('', $data));
        $this->assertEquals('Testing the class \'File\'.', implode('', $file->getChunks(3, false)));
        $this->assertEquals('Testing the class \'File\'.', $file->getRawData());
        $file->append(' Super Cool');
        $this->assertEquals(base64_encode('Testing the class \'File\'. Super Cool'), implode('', $file->getChunks(3, true)));
        $file->append([
            ".\n",
            "Ok",
        ]);
        $this->assertEquals(base64_encode("Testing the class 'File'. Super Cool.\nOk"), implode('', $file->getChunks(3, true)));
        $this->assertEquals('txt', $file->getExtension());
    }
    /**
     * @test
     */
    public function testLastModified00() {
        $file = new File();
        $this->assertEquals(0, $file->getLastModified());
    }
    /**
     * @test
     */
    public function testLastModified01() {
        $file = new File('text-file.txt',ROOT_PATH.DS.'tests'.DS.'files');
        $time = filemtime($file->getAbsolutePath());
        $this->assertEquals($time, $file->getLastModified(null));
        $this->assertEquals(date('Y-m-d H:i:s', $time), $file->getLastModified('Y-m-d H:i:s'));
    }
    /**
     * @test
     */
    public function testRead02() {
        $file = new File('text-file.txt',ROOT_PATH.DS.'tests'.DS.'files');
        $file->read(0, $file->getSize());
        $this->assertEquals('Testing the class \'File\'.', $file->getRawData());
        $this->assertEquals('txt', $file->getExtension());
    }
    /**
     * @test
     */
    public function testRead03() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Reached end of file while trying to read 26 byte(s).');
        $file = new File('text-file.txt',ROOT_PATH.DS.'tests'.DS.'files');
        $file->read(0, $file->getSize() + 1);
    }
    /**
     * @test
     */
    public function testRead04() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Reached end of file while trying to read 6 byte(s).');
        $file = new File('text-file.txt',ROOT_PATH.DS.'tests'.DS.'files');
        $file->read(20, $file->getSize() + 1);
    }
    /**
     * @test
     */
    public function testRead05() {
        $file = new File('text-file.txt',ROOT_PATH.DS.'tests'.DS.'files');
        $file->read(20, $file->getSize());
        $this->assertEquals('ile\'.', $file->getRawData());
    }
    /**
     * @test
     */
    public function testRead06() {
        $file = new File('text-file.txt',ROOT_PATH.DS.'tests'.DS.'files');
        $file->read(2, $file->getSize());
        $this->assertEquals('sting the class \'File\'.', $file->getRawData());
    }
    /**
     * @test
     */
    public function testRead07() {
        $file = new File('text-file.txt',ROOT_PATH.DS.'tests'.DS.'files');
        $file->read(2, 4);
        $this->assertEquals('st', $file->getRawData());
    }
    /**
     * @test
     * @depends testRead00
     */
    public function removeTest() {
        $file = new File(ROOT_PATH.'/not-exist.txt');
        $this->assertFalse($file->remove());
    }
    /**
     * @test
     */
    public function testCreate00() {
        $file = new File(ROOT_PATH.DS.'tests'.DS.'files'.DS.'new.txt');
        $this->assertFalse($file->isExist());
        $file->create();
        $this->assertTrue($file->isExist());
        $file->remove();
        $this->assertFalse($file->isExist());
    }
    /**
     * @test
     */
    public function testCreate01() {
        $this->expectException(FileException::class);
        $file = new File(ROOT_PATH.DS.'tests'.DS.'files'.DS.'not-exist'.DS.'new.txt');
        $this->assertFalse($file->isExist());
        $file->create();
        
    }
    /**
     * @test
     * @depends testCreate01
     */
    public function testCreate02() {
        $file = new File(ROOT_PATH.DS.'tests'.DS.'files'.DS.'not-exist'.DS.'new.txt');
        $this->assertFalse($file->isExist());
        $file->create(true);
        $this->assertTrue($file->isExist());
        $this->assertEquals('{"id":-1,"mime":"text\/plain","name":"new.txt","directory":"'.Json::escapeJSONSpecialChars($file->getDir()).'","sizeInBytes":0,"sizeInKBytes":0,"sizeInMBytes":0}', $file.'');
        $file->remove();
        $this->assertFalse($file->isExist());
    }
    /**
     * @depends test07
     */
    public function testWrite01() {
        $file = new File('hello.txt', ROOT_PATH);
        $file->create();
        $file->setRawData('b');
        $file->write(true, true);
        $file->read();
        $this->assertEquals('b', $file->getRawData());
        $file->setRawData('Hello.');
        $file->write(false);
        $file->read();
        $this->assertEquals('Hello.', $file->getRawData());
        $file->setRawData('World.');
        $file->write(false);
        $this->assertEquals('World.', $file->getRawData());
        $file->setRawData('Hello.');
        $file->write();
        $file->read();
        $this->assertEquals('World.Hello.', $file->getRawData());
        return $file;
    }
    /**
     * @depends test07
     */
    public function testWriteEncoded00() {
        $file = new File('hello-encoded.txt', ROOT_PATH);
        $file->setRawData('b');
        $file->writeEncoded();
        $file2 = new File($file->getAbsolutePath().'.bin');
        $this->assertTrue($file2->isExist());
        $file2->readDecoded();
        $this->assertEquals('b', $file2->getRawData());
    }
    /**
     * @test
     * @param File $file
     * @depends testWrite01
     */
    public function toJson00($file) {
        $j = $file->toJSON();
        $j->setPropsStyle('camel');
        $this->assertEquals('{'
                . '"id":-1,'
                . '"mime":"text\/plain",'
                . '"name":"'.$file->getName().'",'
                . '"directory":"'.Json::escapeJSONSpecialChars($file->getDir()).'",'
                . '"sizeInBytes":12,'
                . '"sizeInKBytes":0.01171875,'
                . '"sizeInMBytes":1.1444091796875E-5'
                . '}',$j.'');
        return $file;
    }
    /**
     * @test
     * @depends toJson00
     * @param File $file
     */
    public function testRemove00($file) {
        $this->assertTrue($file->remove());
        $this->assertFalse(file_exists($file->getAbsolutePath()));
    }
    /**
     * @test
     */
    public function viewTest00() {
        $file = new File('super.txt');
        $file->setRawData('Hello world!');
        $file->view();
        $this->assertEquals([
            'text/plain'
        ], Response::getHeader('content-type'));
        $this->assertEquals('Hello world!', Response::getBody());
        $this->assertEquals([
                'text/plain'
        ], Response::getHeader('content-type'));
        $this->assertEquals([
                'bytes'
        ], Response::getHeader('accept-ranges'));
        $this->assertEquals([
                $file->getSize()
        ], Response::getHeader('content-length'));
        $this->assertEquals([
                'inline; filename="super.txt"'
        ], Response::getHeader('content-disposition'));
        
        Response::clear();
        $file->view(true);
        $this->assertEquals([
                'text/plain'
        ], Response::getHeader('content-type'));
        $this->assertEquals([
                'bytes'
        ], Response::getHeader('accept-ranges'));
        $this->assertEquals([
                $file->getSize()
        ], Response::getHeader('content-length'));
        $this->assertEquals([
                'attachment; filename="super.txt"'
        ], Response::getHeader('content-disposition'));
        Response::clear();
    }
    /**
     * @test
     */
    public function viewTest01() {
        $file = new File('text-file-2.txt',ROOT_PATH.DS.'tests'.DS.'files');
        $file->view();
        $this->assertEquals('Testing the class \'File\'.', Response::getBody());
        
        $this->assertEquals([
                'text/plain'
        ], Response::getHeader('content-type'));
        $this->assertEquals([
                'bytes'
        ], Response::getHeader('accept-ranges'));
        $this->assertEquals([
                $file->getSize()
        ], Response::getHeader('content-length'));
        $this->assertEquals([
                'inline; filename="text-file-2.txt"'
        ], Response::getHeader('content-disposition'));
        Response::clear();
        Response::clear();
    }
}
