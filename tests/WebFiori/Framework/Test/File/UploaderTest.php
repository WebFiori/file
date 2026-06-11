<?php

namespace WebFiori\Framework\Test\File;

use PHPUnit\Framework\TestCase;
use WebFiori\File\Exceptions\FileException;
use WebFiori\File\FileUploader;
use WebFiori\File\UploadedFile;
use WebFiori\Json\Json;
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
        $u = new FileUploader();
        $this->assertEquals('files', $u->getAssociatedFileName());
        $this->assertEquals([], $u->getExts());
        $this->assertEquals('', $u->getUploadDir());
    }
    /**
     * @test
     */
    public function test01() {
        $u = new FileUploader(__DIR__, [
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
    public function test02() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Invalid upload directory: Not?Dir');
        $u = new FileUploader('Not?Dir');
        $this->assertEquals('files', $u->getAssociatedFileName());
        $this->assertEquals([], $u->getExts());
        $this->assertEquals('', $u->getUploadDir());
    }
    /**
     * @test
     */
    public function test03() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Invalid upload directory: Not Exist');
        $u = new FileUploader('Not Exist');
        $this->assertEquals('files', $u->getAssociatedFileName());
        $this->assertEquals([], $u->getExts());
        $this->assertEquals('', $u->getUploadDir());
    }
    /**
     * @test
     */
    public function testAddExt00() {
        $u = new FileUploader();
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
        $u = new FileUploader(__DIR__, [
            'txt'
        ]);
        FileUploader::addTestFile('files', ROOT_PATH.'tests'.DS.'tmp'.DS.'testUpload.txt', true);
        $r = $u->upload();
        $this->assertEquals([
           [
               'name' => 'testUpload.txt',
               'size' => '51',
               'upload-path' => str_replace('/', DS, str_replace('\\', DS, __DIR__)),
               'upload-error' => '',
               'mime' => 'text/plain',
               'is-exist' => false,
               'is-replace' => false,
               'uploaded' => true
           ] 
        ], $r);
        $files = $u->getFiles(true);
        $files[0]->remove();
    }
    /**
     * @test
     */
    public function testUpload01() {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $u = new FileUploader(__DIR__, [
            'txt'
        ]);
        FileUploader::addTestFile('files', ROOT_PATH.'tests'.DS.'tmp'.DS.'testUpload.txt', true);
        $u->upload();
        $r = $u->getFiles(true);
        $file = $r[0];
        $this->assertTrue($file instanceof UploadedFile);
        $this->assertEquals('testUpload.txt',$file->getName());
        $this->assertEquals('testUpload',$file->getNameWithNoExt());
        $this->assertTrue($file->isUploaded());
        $this->assertFalse($file->isReplace());
        $this->assertEquals('text/plain',$file->getMIME());
        $this->assertEquals(str_replace('/', DS, str_replace('\\', DS, __DIR__)),$file->getDir());
        $this->assertEquals(str_replace('/', DS, str_replace('\\', DS, __DIR__)).DS.'testUpload.txt',$file->getAbsolutePath());
        
        $this->assertEquals("",$file->getUploadError());
        $this->assertEquals("{\"id\":-1,\"mime\":\"text\/plain\",\"name\":\"testUpload.txt\""
                . ",\"directory\":\"".Json::escapeJSONSpecialChars($file->getDir())."\",\"sizeInBytes\":51,"
                . "\"sizeInKBytes\":0.0498046875,"
                . "\"sizeInMBytes\":4.8637390136719E-5,"
                . "\"uploaded\":true,"
                . "\"isReplace\":false,"
                . "\"uploadError\":\"\"}", $file.'');
        $file->remove();
    }
    public function testUpload02() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Upload path is not set.');
        $_SERVER['REQUEST_METHOD'] = 'post';
        $u = new FileUploader();
        FileUploader::addTestFile('files', ROOT_PATH.'tests'.DS.'tmp'.DS.'testUpload.txt', true);
        $r = $u->upload();
    }
    /**
     * @test
     */
    public function testToJson00() {
        $u = new FileUploader(__DIR__);
        $this->assertEquals('{"uploadDirectory":"'.Json::escapeJSONSpecialChars($u->getUploadDir()).'",'
                . '"associatedFileName":"files","allowedTypes":[],"files":[]}', $u.'');
        $_SERVER['REQUEST_METHOD'] = 'post';
        $this->assertTrue($u->addExt('txt'));
        $this->assertFalse($u->addExt('   '));
        FileUploader::addTestFile('files', ROOT_PATH.'tests'.DS.'tmp'.DS.'testUpload.txt', true);
        FileUploader::addTestFile('files', ROOT_PATH.'tests'.DS.'tmp'.DS.'not-allowed.xp');
        $r = $u->uploadAsFileObj();
        
        $file1 = $r[0];
        $this->assertTrue($file1 instanceof UploadedFile);
        $this->assertEquals('testUpload.txt',$file1->getName());
        $this->assertEquals('testUpload',$file1->getNameWithNoExt());
        $this->assertTrue($file1->isUploaded());
        $this->assertFalse($file1->isReplace());
        $this->assertEquals('text/plain',$file1->getMIME());
        $this->assertEquals(str_replace('/', DS, str_replace('\\', DS, __DIR__)),$file1->getDir());
        $this->assertEquals(str_replace('/', DS, str_replace('\\', DS, __DIR__)).DS.'testUpload.txt',$file1->getAbsolutePath());
        
        $this->assertEquals("",$file1->getUploadError());
        $file1->remove();
        
        $file2 = $r[1];
        $this->assertTrue($file2 instanceof UploadedFile);
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
                . '"uploaded":true,'
                . '"isReplace":false,'
                . '"uploadError":""},'
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
        $u = new FileUploader(__DIR__, [
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
     * @test
     */
    public function testUploadedFileImplementsFileInterface() {
        $file = new UploadedFile();
        $this->assertInstanceOf(\WebFiori\File\FileInterface::class, $file);
    }
    /**
     * @test
     */
    public function testGetMaxFileSize() {
        $val = ini_get('upload_max_filesize');
        $lastChar = strtoupper($val[strlen($val) - 1]);
        $expected = match ($lastChar) {
            'M' => intval($val) * 1024,
            'K' => intval($val),
            'G' => intval($val) * 1048576,
            default => intval($val) / 1024,
        };
        $this->assertEquals($expected, FileUploader::getMaxFileSize());
    }
    /**
     * @test
     */
    public function testCustomMaxFileSize() {
        $u = new FileUploader(__DIR__, ['txt']);
        $this->assertNull($u->getMaxFileSizeLimit());
        $u->setMaxFileSize(10);
        $this->assertEquals(10, $u->getMaxFileSizeLimit());
    }
    /**
     * @test
     */
    public function testCustomMaxFileSizeRejectsLargeFile() {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $u = new FileUploader(__DIR__, ['txt']);
        $u->setMaxFileSize(10); // 10 bytes limit
        FileUploader::addTestFile('files', ROOT_PATH.'tests'.DS.'tmp'.DS.'testUpload.txt', true);
        $r = $u->upload();
        $this->assertFalse($r[0]['uploaded']);
        $this->assertEquals('file_too_large', $r[0]['upload-error']);
    }
    /**
     * @test
     */
    public function testCustomMaxFileSizeAllowsSmallFile() {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $u = new FileUploader(__DIR__, ['txt']);
        $u->setMaxFileSize(1024); // 1KB limit — file is 51 bytes
        FileUploader::addTestFile('files', ROOT_PATH.'tests'.DS.'tmp'.DS.'testUpload.txt', true);
        $r = $u->upload();
        $this->assertTrue($r[0]['uploaded']);
        $this->assertEquals('', $r[0]['upload-error']);
        // cleanup
        $files = $u->getFiles(true);
        $files[0]->remove();
    }
    /**
     * @test
     */
    public function testSetMaxFileSizeIgnoresInvalid() {
        $u = new FileUploader(__DIR__, ['txt']);
        $u->setMaxFileSize(0);
        $this->assertNull($u->getMaxFileSizeLimit());
        $u->setMaxFileSize(-100);
        $this->assertNull($u->getMaxFileSizeLimit());
    }
    /**
     * @test
     */
    public function testStreamProcessorDefault() {
        $u = new FileUploader(__DIR__, ['txt']);
        $this->assertNull($u->getStreamProcessor());
    }
    /**
     * @test
     */
    public function testStreamProcessorUpload() {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $u = new FileUploader(__DIR__, ['txt']);
        $hashResult = null;

        $u->setStreamProcessor(function(\Generator $chunks, string $destPath) use (&$hashResult) {
            $hash = hash_init('sha256');
            $dest = fopen($destPath, 'wb');

            foreach ($chunks as $chunk) {
                hash_update($hash, $chunk);
                fwrite($dest, $chunk);
            }

            fclose($dest);
            $hashResult = hash_final($hash);
        });

        $this->assertNotNull($u->getStreamProcessor());
        FileUploader::addTestFile('files', ROOT_PATH.'tests'.DS.'tmp'.DS.'testUpload.txt', true);
        $r = $u->upload();
        $this->assertTrue($r[0]['uploaded']);
        $this->assertNotNull($hashResult);
        $this->assertEquals(
            hash('sha256', file_get_contents(ROOT_PATH.'tests'.DS.'tmp'.DS.'testUpload.txt')),
            $hashResult
        );
        // cleanup
        $files = $u->getFiles(true);
        $files[0]->remove();
    }
    /**
     * @test
     */
    public function testStreamProcessorFailure() {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $u = new FileUploader(__DIR__, ['txt']);

        $u->setStreamProcessor(function(\Generator $chunks, string $destPath) {
            throw new \RuntimeException('Processing failed');
        });

        FileUploader::addTestFile('files', ROOT_PATH.'tests'.DS.'tmp'.DS.'testUpload.txt', true);
        $r = $u->upload();
        $this->assertFalse($r[0]['uploaded']);
    }
    /**
     * @test
     */
    public function testOnBeforeUploadAccept() {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $u = new FileUploader(__DIR__, ['txt']);
        $called = false;

        $u->setOnBeforeUpload(function(array $info) use (&$called) {
            $called = true;
            return true;
        });

        FileUploader::addTestFile('files', ROOT_PATH.'tests'.DS.'tmp'.DS.'testUpload.txt', true);
        $r = $u->upload();
        $this->assertTrue($called);
        $this->assertTrue($r[0]['uploaded']);
        $u->getFiles(true)[0]->remove();
    }
    /**
     * @test
     */
    public function testOnBeforeUploadReject() {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $u = new FileUploader(__DIR__, ['txt']);

        $u->setOnBeforeUpload(function(array $info) {
            return false;
        });

        FileUploader::addTestFile('files', ROOT_PATH.'tests'.DS.'tmp'.DS.'testUpload.txt', true);
        $r = $u->upload();
        $this->assertFalse($r[0]['uploaded']);
        $this->assertEquals('rejected_by_callback', $r[0]['upload-error']);
    }
    /**
     * @test
     */
    public function testOnAfterUpload() {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $u = new FileUploader(__DIR__, ['txt']);
        $uploadedFile = null;

        $u->setOnAfterUpload(function(UploadedFile $file) use (&$uploadedFile) {
            $uploadedFile = $file;
        });

        FileUploader::addTestFile('files', ROOT_PATH.'tests'.DS.'tmp'.DS.'testUpload.txt', true);
        $u->upload();
        $this->assertNotNull($uploadedFile);
        $this->assertEquals('testUpload.txt', $uploadedFile->getName());
        $uploadedFile->remove();
    }

    /**
     * @test
     */
    public function testUploadReplaceExisting() {
        $uploadDir = ROOT_PATH . 'tests' . DS . 'tmp';
        $samplePath = ROOT_PATH . 'tests' . DS . 'files' . DS . 'text-file.txt';

        // Create existing file
        $existingPath = $uploadDir . DS . 'text-file.txt';
        file_put_contents($existingPath, 'old content');

        $u = new FileUploader($uploadDir, ['txt']);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        FileUploader::addTestFile('files', $samplePath, true);

        $files = $u->uploadAsFileObj(true);
        $this->assertCount(1, $files);
        $this->assertTrue($files[0]->isUploaded());
        $this->assertTrue($files[0]->isReplace());
        $files[0]->remove();
    }

    /**
     * @test
     */
    public function testUploadExistingNoReplace() {
        $uploadDir = ROOT_PATH . 'tests' . DS . 'tmp';
        $samplePath = ROOT_PATH . 'tests' . DS . 'files' . DS . 'text-file.txt';

        // Create existing file
        $existingPath = $uploadDir . DS . 'text-file.txt';
        file_put_contents($existingPath, 'old content');

        $u = new FileUploader($uploadDir, ['txt']);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        FileUploader::addTestFile('files', $samplePath, true);

        $files = $u->uploadAsFileObj(false);
        $this->assertCount(1, $files);
        $this->assertFalse($files[0]->isUploaded());
        $this->assertNotEmpty($files[0]->getUploadError());
        @unlink($existingPath);
    }

    /**
     * @test
     */
    public function testGetFilesAsArray() {
        $uploadDir = ROOT_PATH . 'tests' . DS . 'tmp';
        $samplePath = ROOT_PATH . 'tests' . DS . 'files' . DS . 'text-file.txt';

        $u = new FileUploader($uploadDir, ['txt']);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        FileUploader::addTestFile('files', $samplePath, true);
        $u->upload();

        $asArr = $u->getFiles(false);
        $this->assertIsArray($asArr);
        $this->assertCount(1, $asArr);
        $this->assertIsArray($asArr[0]);
        $this->assertArrayHasKey('name', $asArr[0]);

        $asObj = $u->getFiles(true);
        $this->assertCount(1, $asObj);
        $this->assertInstanceOf(UploadedFile::class, $asObj[0]);
        $asObj[0]->remove();
    }

    /**
     * @test
     */
    public function testToJSON() {
        $uploadDir = ROOT_PATH . 'tests' . DS . 'tmp';
        $u = new FileUploader($uploadDir, ['txt', 'png']);
        $json = $u->toJSON();
        $this->assertInstanceOf(Json::class, $json);
    }

    /**
     * @test
     */
    public function testUploadNotAllowedExtension() {
        $uploadDir = ROOT_PATH . 'tests' . DS . 'tmp';
        $samplePath = ROOT_PATH . 'tests' . DS . 'tmp' . DS . 'not-allowed.xp';

        $u = new FileUploader($uploadDir, ['txt']);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        FileUploader::addTestFile('files', $samplePath, true);

        $files = $u->uploadAsFileObj();
        $this->assertCount(1, $files);
        $this->assertFalse($files[0]->isUploaded());
    }

    /**
     * @test
     */
    public function testUploadWithMaxSizeExceeded() {
        $uploadDir = ROOT_PATH . 'tests' . DS . 'tmp';
        $samplePath = ROOT_PATH . 'tests' . DS . 'files' . DS . 'text-file.txt';

        $u = new FileUploader($uploadDir, ['txt']);
        $u->setMaxFileSize(1); // 1 byte
        $_SERVER['REQUEST_METHOD'] = 'POST';
        FileUploader::addTestFile('files', $samplePath, true);

        $files = $u->uploadAsFileObj();
        $this->assertCount(1, $files);
        $this->assertFalse($files[0]->isUploaded());
    }

    /**
     * @test
     */
    public function testGetMaxFileSizeReturnsPositive() {
        $size = FileUploader::getMaxFileSize();
        $this->assertIsInt($size);
        $this->assertGreaterThan(0, $size);
    }

    /**
     * @test
     */
    public function testUploadWithBeforeCallbackReject() {
        $uploadDir = ROOT_PATH . 'tests' . DS . 'tmp';
        $samplePath = ROOT_PATH . 'tests' . DS . 'files' . DS . 'text-file.txt';

        $u = new FileUploader($uploadDir, ['txt']);
        $u->setOnBeforeUpload(function ($info) {
            return false;
        });
        $_SERVER['REQUEST_METHOD'] = 'POST';
        FileUploader::addTestFile('files', $samplePath, true);

        $files = $u->uploadAsFileObj();
        $this->assertCount(1, $files);
        $this->assertFalse($files[0]->isUploaded());
    }

    /**
     * @test
     */
    public function testUploadWithAfterCallback() {
        $uploadDir = ROOT_PATH . 'tests' . DS . 'tmp';
        $samplePath = ROOT_PATH . 'tests' . DS . 'files' . DS . 'text-file.txt';
        $called = false;

        $u = new FileUploader($uploadDir, ['txt']);
        $u->setOnAfterUpload(function ($file) use (&$called) {
            $called = true;
        });
        $_SERVER['REQUEST_METHOD'] = 'POST';
        FileUploader::addTestFile('files', $samplePath, true);

        $files = $u->uploadAsFileObj();
        $this->assertTrue($called);
        $files[0]->remove();
    }

    /**
     * @test
     */
    public function testMultiFileUpload() {
        $uploadDir = ROOT_PATH . 'tests' . DS . 'tmp';
        $samplePath1 = ROOT_PATH . 'tests' . DS . 'files' . DS . 'text-file.txt';
        $samplePath2 = ROOT_PATH . 'tests' . DS . 'files' . DS . 'text-file-2.txt';

        $u = new FileUploader($uploadDir, ['txt']);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_FILES = [];
        FileUploader::addTestFile('files', $samplePath1, true);
        FileUploader::addTestFile('files', $samplePath2, false);

        $files = $u->uploadAsFileObj();
        $this->assertCount(2, $files);
        $this->assertTrue($files[0]->isUploaded());
        $this->assertTrue($files[1]->isUploaded());
        $files[0]->remove();
        $files[1]->remove();
    }

    /**
     * @test
     */
    public function testAddTestFileEmptyIndex() {
        $_FILES = [];
        FileUploader::addTestFile('', '/some/path.txt', true);
        $this->assertEmpty($_FILES);
    }

    /**
     * @test
     */
    public function testAddTestFileNotFound() {
        $this->expectException(FileException::class);
        FileUploader::addTestFile('files', '/nonexistent/file.txt', true);
    }

    /**
     * @test
     */
    public function testUploadViaPostFileField() {
        $uploadDir = ROOT_PATH . 'tests' . DS . 'tmp';
        $samplePath = ROOT_PATH . 'tests' . DS . 'files' . DS . 'text-file.txt';

        $u = new FileUploader($uploadDir, ['txt']);
        $u->setAssociatedFileName('my_field');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['file'] = 'my_field';
        FileUploader::addTestFile('my_field', $samplePath, true);

        $files = $u->uploadAsFileObj();
        $this->assertCount(1, $files);
        $this->assertTrue($files[0]->isUploaded());
        $files[0]->remove();
        unset($_POST['file']);
    }

    /**
     * @test
     */
    public function testAbstractUploaderGetCallbacks() {
        $u = new FileUploader();
        $this->assertNull($u->getOnBeforeUpload());
        $this->assertNull($u->getOnAfterUpload());

        $before = function () { return true; };
        $after = function () {};
        $u->setOnBeforeUpload($before);
        $u->setOnAfterUpload($after);

        $this->assertSame($before, $u->getOnBeforeUpload());
        $this->assertSame($after, $u->getOnAfterUpload());
    }

    /**
     * @test
     */
    public function testSetUploadDirEmpty() {
        $this->expectException(FileException::class);
        $u = new FileUploader();
        $u->setUploadDir('');
    }
}
