<?php

namespace WebFiori\Framework\Test\File;

use PHPUnit\Framework\TestCase;
use WebFiori\File\AbstractUploader;
use WebFiori\File\Exceptions\FileException;
use WebFiori\File\ResumableUploader;
use WebFiori\File\UploadedFile;

class ResumableUploaderTest extends TestCase {
    private static string $testDir;
    private static string $inputFile1;
    private static string $inputFile2;
    private static string $inputFile3;

    public static function setUpBeforeClass(): void {
        self::$testDir = ROOT_PATH . 'tests' . DS . 'tmp';
        self::$inputFile1 = self::$testDir . DS . 'input-chunk1.bin';
        self::$inputFile2 = self::$testDir . DS . 'input-chunk2.bin';
        self::$inputFile3 = self::$testDir . DS . 'input-chunk3.bin';
        file_put_contents(self::$inputFile1, 'AAAA');
        file_put_contents(self::$inputFile2, 'BBBB');
        file_put_contents(self::$inputFile3, 'CCCC');
    }

    public static function tearDownAfterClass(): void {
        @unlink(self::$inputFile1);
        @unlink(self::$inputFile2);
        @unlink(self::$inputFile3);
        $partialDir = self::$testDir . DS . '.partial';

        if (is_dir($partialDir)) {
            foreach (glob($partialDir . DS . '*') as $f) {
                @unlink($f);
            }
            @rmdir($partialDir);
        }

        foreach (glob(self::$testDir . DS . 'resumable-*') as $f) {
            @unlink($f);
        }
    }

    protected function tearDown(): void {
        $partialDir = self::$testDir . DS . '.partial';

        if (is_dir($partialDir)) {
            foreach (glob($partialDir . DS . '*') as $f) {
                @unlink($f);
            }
        }

        foreach (glob(self::$testDir . DS . 'resumable-*') as $f) {
            @unlink($f);
        }
    }

    /**
     * @test
     */
    public function testExtendsAbstractUploader() {
        $u = new ResumableUploader(self::$testDir, ['txt'], self::$inputFile1);
        $this->assertInstanceOf(AbstractUploader::class, $u);
    }

    /**
     * @test
     */
    public function testReceiveChunkEmptyUploadId() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Upload ID cannot be empty.');
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $u->receiveChunk('', 'test.txt');
    }

    /**
     * @test
     */
    public function testReceiveChunkNoUploadDir() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Upload path is not set.');
        $u = new ResumableUploader('', [], self::$inputFile1);
        $u->receiveChunk('abc123', 'test.txt');
    }

    /**
     * @test
     */
    public function testReceiveChunkInvalidExtension() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('File type not allowed.');
        $u = new ResumableUploader(self::$testDir, ['png'], self::$inputFile1);
        $u->receiveChunk('abc123', 'resumable-test.txt');
    }

    /**
     * @test
     */
    public function testReceiveChunkEmptyFilenameAfterSanitize() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Filename is empty after sanitization.');
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $u->receiveChunk('abc123', '...');
    }

    /**
     * @test
     */
    public function testReceiveSingleChunkNotLast() {
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $result = $u->receiveChunk('upload1', 'resumable-single.txt', false);

        $this->assertEquals(4, $result['offset']);
        $this->assertFalse($result['complete']);
        $this->assertNull($result['file']);
    }

    /**
     * @test
     */
    public function testReceiveMultipleChunksAndFinalize() {
        $u1 = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $result1 = $u1->receiveChunk('upload2', 'resumable-multi.txt', false);
        $this->assertEquals(4, $result1['offset']);
        $this->assertFalse($result1['complete']);

        $u2 = new ResumableUploader(self::$testDir, [], self::$inputFile2);
        $result2 = $u2->receiveChunk('upload2', 'resumable-multi.txt', false);
        $this->assertEquals(8, $result2['offset']);
        $this->assertFalse($result2['complete']);

        $u3 = new ResumableUploader(self::$testDir, [], self::$inputFile3);
        $result3 = $u3->receiveChunk('upload2', 'resumable-multi.txt', true);
        $this->assertEquals(12, $result3['offset']);
        $this->assertTrue($result3['complete']);
        $this->assertInstanceOf(UploadedFile::class, $result3['file']);
        $this->assertEquals('resumable-multi.txt', $result3['file']->getName());
        $this->assertTrue($result3['file']->isUploaded());

        $finalPath = self::$testDir . DS . 'resumable-multi.txt';
        $this->assertEquals('AAAABBBBCCCC', file_get_contents($finalPath));
    }

    /**
     * @test
     */
    public function testGetOffset() {
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $u->receiveChunk('offset-test', 'resumable-offset.txt', false);

        $u2 = new ResumableUploader(self::$testDir, [], self::$inputFile2);
        $this->assertEquals(4, $u2->getOffset('offset-test', 'resumable-offset.txt'));
    }

    /**
     * @test
     */
    public function testGetOffsetNonExistent() {
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $this->assertEquals(0, $u->getOffset('no-such-id', 'no-such-file.txt'));
    }

    /**
     * @test
     */
    public function testCancel() {
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $u->receiveChunk('cancel-test', 'resumable-cancel.txt', false);
        $this->assertEquals(4, $u->getOffset('cancel-test', 'resumable-cancel.txt'));

        $u->cancel('cancel-test', 'resumable-cancel.txt');
        $this->assertEquals(0, $u->getOffset('cancel-test', 'resumable-cancel.txt'));
    }

    /**
     * @test
     */
    public function testCancelNonExistent() {
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        // Should not throw
        $u->cancel('no-such-id', 'no-such-file.txt');
        $this->assertEquals(0, $u->getOffset('no-such-id', 'no-such-file.txt'));
    }

    /**
     * @test
     */
    public function testCleanStale() {
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $u->receiveChunk('stale1', 'resumable-stale1.txt', false);
        $u2 = new ResumableUploader(self::$testDir, [], self::$inputFile2);
        $u2->receiveChunk('stale2', 'resumable-stale2.txt', false);

        // Set mtime to 2 hours ago for one file
        $partialDir = self::$testDir . DS . '.partial';
        $stalePath = $partialDir . DS . 'stale1_resumable-stale1.txt';
        touch($stalePath, time() - 7200);

        $u3 = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $removed = $u3->cleanStale(3600); // Older than 1 hour

        $this->assertEquals(1, $removed);
        $this->assertFalse(file_exists($stalePath));
        // stale2 should still exist
        $this->assertTrue(file_exists($partialDir . DS . 'stale2_resumable-stale2.txt'));
    }

    /**
     * @test
     */
    public function testCleanStaleNoPartialDir() {
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $partialDir = self::$testDir . DS . '.partial';

        if (is_dir($partialDir)) {
            foreach (glob($partialDir . DS . '*') as $f) {
                unlink($f);
            }
            rmdir($partialDir);
        }

        $this->assertEquals(0, $u->cleanStale(3600));
    }

    /**
     * @test
     */
    public function testExceedsSizeLimitDuringChunk() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('File exceeds size limit.');
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $u->setMaxFileSize(2); // Only 2 bytes allowed, chunk is 4
        $u->receiveChunk('size-test', 'resumable-size.txt', false);
    }

    /**
     * @test
     */
    public function testExceedsSizeLimitOnSecondChunk() {
        $u1 = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $u1->setMaxFileSize(6); // Allow first chunk (4 bytes)
        $u1->receiveChunk('size-test2', 'resumable-size2.txt', false);

        $this->expectException(FileException::class);
        $this->expectExceptionMessage('File exceeds size limit.');
        $u2 = new ResumableUploader(self::$testDir, [], self::$inputFile2);
        $u2->setMaxFileSize(6); // Second chunk would make it 8, exceeds 6
        $u2->receiveChunk('size-test2', 'resumable-size2.txt', false);
    }

    /**
     * @test
     */
    public function testBeforeUploadCallbackRejects() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Upload rejected by callback.');
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $u->setOnBeforeUpload(function (array $info) {
            return false;
        });
        $u->receiveChunk('cb-reject', 'resumable-reject.txt', false);
    }

    /**
     * @test
     */
    public function testBeforeUploadCallbackOnlyFiredOnFirstChunk() {
        $callCount = 0;
        $u1 = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $u1->setOnBeforeUpload(function (array $info) use (&$callCount) {
            $callCount++;

            return true;
        });
        $u1->receiveChunk('cb-once', 'resumable-cbonce.txt', false);
        $this->assertEquals(1, $callCount);

        $u2 = new ResumableUploader(self::$testDir, [], self::$inputFile2);
        $u2->setOnBeforeUpload(function (array $info) use (&$callCount) {
            $callCount++;

            return true;
        });
        $u2->receiveChunk('cb-once', 'resumable-cbonce.txt', true);
        // Should NOT fire again since partial file exists
        $this->assertEquals(1, $callCount);
    }

    /**
     * @test
     */
    public function testAfterUploadCallbackOnFinalize() {
        $called = null;
        $u1 = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $u1->setOnAfterUpload(function (UploadedFile $file) use (&$called) {
            $called = $file->getName();
        });
        $u1->receiveChunk('cb-after', 'resumable-cbafter.txt', false);
        $this->assertNull($called);

        $u2 = new ResumableUploader(self::$testDir, [], self::$inputFile2);
        $u2->setOnAfterUpload(function (UploadedFile $file) use (&$called) {
            $called = $file->getName();
        });
        $u2->receiveChunk('cb-after', 'resumable-cbafter.txt', true);
        $this->assertEquals('resumable-cbafter.txt', $called);
    }

    /**
     * @test
     */
    public function testStreamProcessorOnFinalize() {
        $u1 = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $u1->receiveChunk('proc-test', 'resumable-proc.txt', false);

        $hashResult = null;
        $u2 = new ResumableUploader(self::$testDir, [], self::$inputFile2);
        $u2->setStreamProcessor(function (\Generator $chunks, string $destPath) use (&$hashResult) {
            $hash = hash_init('sha256');
            $dest = fopen($destPath, 'wb');

            foreach ($chunks as $chunk) {
                hash_update($hash, $chunk);
                fwrite($dest, $chunk);
            }

            fclose($dest);
            $hashResult = hash_final($hash);
        });
        $result = $u2->receiveChunk('proc-test', 'resumable-proc.txt', true);

        $this->assertTrue($result['complete']);
        $this->assertNotNull($hashResult);
        $this->assertEquals(hash('sha256', 'AAAABBBB'), $hashResult);

        $finalPath = self::$testDir . DS . 'resumable-proc.txt';
        $this->assertEquals('AAAABBBB', file_get_contents($finalPath));
    }

    /**
     * @test
     */
    public function testReceiveChunkWithAllowedExtension() {
        $u = new ResumableUploader(self::$testDir, ['txt'], self::$inputFile1);
        $result = $u->receiveChunk('ext-test', 'resumable-ext.txt', true);
        $this->assertTrue($result['complete']);
        $this->assertEquals('resumable-ext.txt', $result['file']->getName());
    }

    /**
     * @test
     */
    public function testResolveFilenameFromHeader() {
        $_SERVER['HTTP_X_FILENAME'] = 'resumable-header.txt';
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $result = $u->receiveChunk('header-test', null, true);
        $this->assertEquals('resumable-header.txt', $result['file']->getName());
        unset($_SERVER['HTTP_X_FILENAME']);
    }

    /**
     * @test
     */
    public function testResolveFilenameDefault() {
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $result = $u->receiveChunk('default-name', null, true);
        $this->assertEquals('upload.bin', $result['file']->getName());
    }

    /**
     * @test
     */
    public function testResolveFilenameFromContentDisposition() {
        $_SERVER['HTTP_CONTENT_DISPOSITION'] = 'attachment; filename="resumable-disp.txt"';
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $result = $u->receiveChunk('disp-test', null, true);
        $this->assertEquals('resumable-disp.txt', $result['file']->getName());
        unset($_SERVER['HTTP_CONTENT_DISPOSITION']);
    }

    /**
     * @test
     */
    public function testFilenameSanitizedInGetOffset() {
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $u->receiveChunk('sanitize-test', 'resumable-clean.txt', false);
        // Passing unsanitized name should still find the file
        $this->assertEquals(4, $u->getOffset('sanitize-test', '../../resumable-clean.txt'));
    }

    /**
     * @test
     */
    public function testFilenameSanitizedInCancel() {
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $u->receiveChunk('sanitize-cancel', 'resumable-scancel.txt', false);
        $this->assertEquals(4, $u->getOffset('sanitize-cancel', 'resumable-scancel.txt'));

        $u->cancel('sanitize-cancel', '../../resumable-scancel.txt');
        $this->assertEquals(0, $u->getOffset('sanitize-cancel', 'resumable-scancel.txt'));
    }

    /**
     * @test
     */
    public function testFinalizeReplacesExistingFile() {
        $finalPath = self::$testDir . DS . 'resumable-replace.txt';
        file_put_contents($finalPath, 'old content');

        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $result = $u->receiveChunk('replace-test', 'resumable-replace.txt', true);

        $this->assertTrue($result['complete']);
        $this->assertEquals('AAAA', file_get_contents($finalPath));
    }

    /**
     * @test
     */
    public function testReceiveChunkInputStreamFails() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Unable to open input stream.');
        $u = new ResumableUploader(self::$testDir, [], '/nonexistent/path/input.bin');
        $u->receiveChunk('fail-input', 'resumable-fail.txt', false);
    }

    /**
     * @test
     */
    public function testBeforeUploadReceivesUploadId() {
        $receivedInfo = null;
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $u->setOnBeforeUpload(function (array $info) use (&$receivedInfo) {
            $receivedInfo = $info;

            return true;
        });
        $u->receiveChunk('info-test', 'resumable-info.txt', false);

        $this->assertNotNull($receivedInfo);
        $this->assertEquals('resumable-info.txt', $receivedInfo['name']);
        $this->assertEquals(self::$testDir, $receivedInfo['upload-path']);
        $this->assertEquals('info-test', $receivedInfo['upload-id']);
    }

    /**
     * @test
     */
    public function testSingleChunkCompleteUpload() {
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        $result = $u->receiveChunk('one-shot', 'resumable-oneshot.txt', true);

        $this->assertTrue($result['complete']);
        $this->assertEquals(4, $result['offset']);
        $this->assertInstanceOf(UploadedFile::class, $result['file']);
        $this->assertEquals('AAAA', file_get_contents(self::$testDir . DS . 'resumable-oneshot.txt'));
    }

    /**
     * @test
     */
    public function testPartialDirNotWritable() {
        $partialDir = self::$testDir . DS . '.partial';

        if (!is_dir($partialDir)) {
            mkdir($partialDir, 0755, true);
        }

        // Create a file that blocks creating the partial file (directory with same name)
        $blockPath = $partialDir . DS . 'blocked_resumable-blocked.txt';
        @mkdir($blockPath, 0755, true);

        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Unable to open partial file for writing.');
        $u = new ResumableUploader(self::$testDir, [], self::$inputFile1);
        try {
            $u->receiveChunk('blocked', 'resumable-blocked.txt', false);
        } finally {
            @rmdir($blockPath);
        }
    }

    /**
     * @test
     */
    public function testEmptyInputFile() {
        $emptyInput = self::$testDir . DS . 'input-empty.bin';
        file_put_contents($emptyInput, '');

        $u = new ResumableUploader(self::$testDir, [], $emptyInput);
        $result = $u->receiveChunk('empty-input', 'resumable-empty.txt', false);
        $this->assertEquals(0, $result['offset']);
        @unlink($emptyInput);
    }
}
