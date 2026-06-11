<?php

namespace WebFiori\Framework\Test\File;

use PHPUnit\Framework\TestCase;
use WebFiori\File\AbstractUploader;
use WebFiori\File\Exceptions\FileException;
use WebFiori\File\StreamingUploader;
use WebFiori\File\UploadedFile;

class StreamingUploaderTest extends TestCase {
    private static string $testDir;
    private static string $inputFile;

    public static function setUpBeforeClass(): void {
        self::$testDir = ROOT_PATH . 'tests' . DS . 'tmp';
        self::$inputFile = self::$testDir . DS . 'streaming-input.bin';
        file_put_contents(self::$inputFile, 'streaming upload content');
    }

    public static function tearDownAfterClass(): void {
        if (file_exists(self::$inputFile)) {
            unlink(self::$inputFile);
        }
        foreach (glob(self::$testDir . DS . 'streamed-*') as $f) {
            unlink($f);
        }
    }
    /**
     * @test
     */
    public function testExtendsAbstractUploader() {
        $u = new StreamingUploader(self::$testDir, ['txt'], self::$inputFile);
        $this->assertInstanceOf(AbstractUploader::class, $u);
    }
    /**
     * @test
     */
    public function testReceiveBasic() {
        $u = new StreamingUploader(self::$testDir, [], self::$inputFile);
        $file = $u->receive('streamed-basic.txt');
        $this->assertInstanceOf(UploadedFile::class, $file);
        $this->assertEquals('streamed-basic.txt', $file->getName());
        $this->assertTrue($file->isUploaded());
        $this->assertEquals('streaming upload content', file_get_contents($file->getAbsolutePath()));
        $file->remove();
    }
    /**
     * @test
     */
    public function testReceiveNoUploadDir() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Upload path is not set.');
        $u = new StreamingUploader('', [], self::$inputFile);
        $u->receive('test.txt');
    }
    /**
     * @test
     */
    public function testReceiveInvalidExtension() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('File type not allowed.');
        $u = new StreamingUploader(self::$testDir, ['png'], self::$inputFile);
        $u->receive('streamed-invalid.txt');
    }
    /**
     * @test
     */
    public function testReceiveAllowedExtension() {
        $u = new StreamingUploader(self::$testDir, ['txt'], self::$inputFile);
        $file = $u->receive('streamed-allowed.txt');
        $this->assertTrue($file->isUploaded());
        $file->remove();
    }
    /**
     * @test
     */
    public function testReceiveExceedsSizeLimit() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('File exceeds size limit.');
        $u = new StreamingUploader(self::$testDir, [], self::$inputFile);
        $u->setMaxFileSize(5); // 5 bytes, content is 24
        $u->receive('streamed-toobig.txt');
    }
    /**
     * @test
     */
    public function testReceiveWithinSizeLimit() {
        $u = new StreamingUploader(self::$testDir, [], self::$inputFile);
        $u->setMaxFileSize(1024);
        $file = $u->receive('streamed-sizeok.txt');
        $this->assertTrue($file->isUploaded());
        $file->remove();
    }
    /**
     * @test
     */
    public function testReceiveWithStreamProcessor() {
        $u = new StreamingUploader(self::$testDir, [], self::$inputFile);
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

        $file = $u->receive('streamed-hashed.txt');
        $this->assertNotNull($hashResult);
        $this->assertEquals(hash('sha256', 'streaming upload content'), $hashResult);
        $file->remove();
    }
    /**
     * @test
     */
    public function testReceiveBeforeCallbackRejects() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Upload rejected by callback.');
        $u = new StreamingUploader(self::$testDir, [], self::$inputFile);
        $u->setOnBeforeUpload(function(array $info) {
            return false;
        });
        $u->receive('streamed-rejected.txt');
    }
    /**
     * @test
     */
    public function testReceiveAfterCallback() {
        $u = new StreamingUploader(self::$testDir, [], self::$inputFile);
        $called = null;

        $u->setOnAfterUpload(function(UploadedFile $file) use (&$called) {
            $called = $file->getName();
        });

        $file = $u->receive('streamed-after.txt');
        $this->assertEquals('streamed-after.txt', $called);
        $file->remove();
    }
    /**
     * @test
     */
    public function testReceiveFromXFilenameHeader() {
        $_SERVER['HTTP_X_FILENAME'] = 'header-name.txt';
        $u = new StreamingUploader(self::$testDir, [], self::$inputFile);
        $file = $u->receive();
        $this->assertEquals('header-name.txt', $file->getName());
        $file->remove();
        unset($_SERVER['HTTP_X_FILENAME']);
    }
    /**
     * @test
     */
    public function testReceiveDefaultFilename() {
        $u = new StreamingUploader(self::$testDir, [], self::$inputFile);
        $file = $u->receive();
        $this->assertEquals('upload.bin', $file->getName());
        $file->remove();
    }
    /**
     * @test
     */
    public function testReceiveSanitizesFilename() {
        $u = new StreamingUploader(self::$testDir, [], self::$inputFile);
        $file = $u->receive('../../evil<script>.txt');
        $this->assertEquals('evil_script_.txt', $file->getName());
        $file->remove();
    }
    /**
     * @test
     */
    public function testReceiveEmptyFilenameAfterSanitize() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Filename is empty after sanitization.');
        $u = new StreamingUploader(self::$testDir, [], self::$inputFile);
        $u->receive('...');
    }

    /**
     * @test
     */
    public function testReceiveContentLengthExceedsLimit() {
        $_SERVER['CONTENT_LENGTH'] = '1000';
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('File exceeds size limit.');
        try {
            $u = new StreamingUploader(self::$testDir, [], self::$inputFile);
            $u->setMaxFileSize(50);
            $u->receive('streamed-clength.txt');
        } finally {
            unset($_SERVER['CONTENT_LENGTH']);
        }
    }

    /**
     * @test
     */
    public function testReceiveFromContentDispositionHeader() {
        $_SERVER['HTTP_CONTENT_DISPOSITION'] = 'attachment; filename="disp-file.txt"';
        $u = new StreamingUploader(self::$testDir, [], self::$inputFile);
        $file = $u->receive();
        $this->assertEquals('disp-file.txt', $file->getName());
        $file->remove();
        unset($_SERVER['HTTP_CONTENT_DISPOSITION']);
    }

    /**
     * @test
     */
    public function testReceiveInvalidInputSource() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Unable to open input stream.');
        $u = new StreamingUploader(self::$testDir, [], '/nonexistent/path.bin');
        $u->receive('fail.txt');
    }
}
