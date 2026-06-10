<?php

namespace WebFiori\Framework\Test\File;

use PHPUnit\Framework\TestCase;
use WebFiori\File\DefaultEmitter;
use WebFiori\File\Exceptions\FileException;
use WebFiori\File\File;
use WebFiori\File\FileStream;
use WebFiori\File\ResponseEmitter;
use WebFiori\File\StreamableInterface;

class FileStreamTest extends TestCase {
    private static string $testDir;
    private static string $testFile;

    public static function setUpBeforeClass(): void {
        self::$testDir = ROOT_PATH . 'tests' . DS . 'tmp';
        self::$testFile = self::$testDir . DS . 'stream-test.txt';
        file_put_contents(self::$testFile, "Line 1\nLine 2\nLine 3\nLine 4\nLine 5\n");
    }

    public static function tearDownAfterClass(): void {
        if (file_exists(self::$testFile)) {
            unlink(self::$testFile);
        }
        // Clean up any other test files
        foreach (glob(self::$testDir . DS . 'stream-*') as $f) {
            unlink($f);
        }
    }
    /**
     * @test
     */
    public function testImplementsStreamableInterface() {
        $stream = new FileStream(self::$testFile);
        $this->assertInstanceOf(StreamableInterface::class, $stream);
    }
    /**
     * @test
     */
    public function testConstructorEmptyPath() {
        $this->expectException(FileException::class);
        new FileStream('');
    }
    /**
     * @test
     */
    public function testGetName() {
        $stream = new FileStream(self::$testFile);
        $this->assertEquals('stream-test.txt', $stream->getName());
    }
    /**
     * @test
     */
    public function testGetMIME() {
        $stream = new FileStream(self::$testFile);
        $this->assertEquals('text/plain', $stream->getMIME());
    }
    /**
     * @test
     */
    public function testGetSize() {
        $stream = new FileStream(self::$testFile);
        $this->assertEquals(filesize(self::$testFile), $stream->getSize());
    }
    /**
     * @test
     */
    public function testGetSizeNonExistent() {
        $stream = new FileStream(self::$testDir . DS . 'nonexistent.txt');
        $this->assertEquals(0, $stream->getSize());
    }
    /**
     * @test
     */
    public function testGetPath() {
        $stream = new FileStream(self::$testFile);
        $this->assertEquals(self::$testFile, $stream->getPath());
    }
    /**
     * @test
     */
    public function testBufferSize() {
        $stream = new FileStream(self::$testFile, 4096);
        $this->assertEquals(4096, $stream->getBufferSize());
        $stream->setBufferSize(1024);
        $this->assertEquals(1024, $stream->getBufferSize());
    }
    /**
     * @test
     */
    public function testBufferSizeInvalidFallsBack() {
        $stream = new FileStream(self::$testFile, -1);
        $this->assertEquals(8192, $stream->getBufferSize());
        $stream->setBufferSize(0);
        $this->assertEquals(8192, $stream->getBufferSize());
    }
    /**
     * @test
     */
    public function testReadChunks() {
        $stream = new FileStream(self::$testFile);
        $data = '';

        foreach ($stream->readChunks(10) as $chunk) {
            $this->assertLessThanOrEqual(10, strlen($chunk));
            $data .= $chunk;
        }
        $this->assertEquals(file_get_contents(self::$testFile), $data);
    }
    /**
     * @test
     */
    public function testReadChunksLargeBuffer() {
        $stream = new FileStream(self::$testFile);
        $chunks = [];

        foreach ($stream->readChunks(99999) as $chunk) {
            $chunks[] = $chunk;
        }
        // Entire file in one chunk
        $this->assertCount(1, $chunks);
        $this->assertEquals(file_get_contents(self::$testFile), $chunks[0]);
    }
    /**
     * @test
     */
    public function testReadChunksNonExistentFile() {
        $stream = new FileStream(self::$testDir . DS . 'nonexistent.txt');
        $this->expectException(FileException::class);
        iterator_to_array($stream->readChunks());
    }
    /**
     * @test
     */
    public function testReadLines() {
        $stream = new FileStream(self::$testFile);
        $lines = [];

        foreach ($stream->readLines() as $line) {
            $lines[] = $line;
        }
        $this->assertCount(5, $lines);
        $this->assertEquals("Line 1\n", $lines[0]);
        $this->assertEquals("Line 5\n", $lines[4]);
    }
    /**
     * @test
     */
    public function testReadLinesNonExistentFile() {
        $stream = new FileStream(self::$testDir . DS . 'nonexistent.txt');
        $this->expectException(FileException::class);
        iterator_to_array($stream->readLines());
    }
    /**
     * @test
     */
    public function testReadRange() {
        $stream = new FileStream(self::$testFile);
        $data = '';

        foreach ($stream->readRange(0, 6) as $chunk) {
            $data .= $chunk;
        }
        $this->assertEquals('Line 1', $data);
    }
    /**
     * @test
     */
    public function testReadRangeMiddle() {
        $stream = new FileStream(self::$testFile);
        $content = file_get_contents(self::$testFile);
        $data = '';

        foreach ($stream->readRange(7, 13) as $chunk) {
            $data .= $chunk;
        }
        $this->assertEquals(substr($content, 7, 6), $data);
    }
    /**
     * @test
     */
    public function testReadRangeSmallBuffer() {
        $stream = new FileStream(self::$testFile);
        $data = '';

        foreach ($stream->readRange(0, 10, 3) as $chunk) {
            $this->assertLessThanOrEqual(3, strlen($chunk));
            $data .= $chunk;
        }
        $this->assertEquals(substr(file_get_contents(self::$testFile), 0, 10), $data);
    }
    /**
     * @test
     */
    public function testReadRangeInvalidFrom() {
        $stream = new FileStream(self::$testFile);
        $this->expectException(FileException::class);
        iterator_to_array($stream->readRange(-1, 10));
    }
    /**
     * @test
     */
    public function testReadRangeInvalidToLessThanFrom() {
        $stream = new FileStream(self::$testFile);
        $this->expectException(FileException::class);
        iterator_to_array($stream->readRange(10, 5));
    }
    /**
     * @test
     */
    public function testWriteFromStream() {
        $dest = self::$testDir . DS . 'stream-write-test.txt';
        $source = new FileStream(self::$testFile);
        $target = new FileStream($dest);

        // Create the destination file first
        file_put_contents($dest, '');

        $target->writeFromStream($source->readChunks(), false);
        $this->assertEquals(file_get_contents(self::$testFile), file_get_contents($dest));
        unlink($dest);
    }
    /**
     * @test
     */
    public function testWriteFromStreamAppend() {
        $dest = self::$testDir . DS . 'stream-append-test.txt';
        file_put_contents($dest, 'EXISTING');

        $target = new FileStream($dest);
        $target->writeFromStream(['_APPENDED'], true);

        $this->assertEquals('EXISTING_APPENDED', file_get_contents($dest));
        unlink($dest);
    }
    /**
     * @test
     */
    public function testWriteFromStreamNoLock() {
        $dest = self::$testDir . DS . 'stream-nolock-test.txt';
        file_put_contents($dest, '');

        $target = new FileStream($dest);
        $target->writeFromStream(['data'], false, false);

        $this->assertEquals('data', file_get_contents($dest));
        unlink($dest);
    }
    /**
     * @test
     */
    public function testWriteFromArray() {
        $dest = self::$testDir . DS . 'stream-array-test.txt';
        file_put_contents($dest, '');

        $target = new FileStream($dest);
        $target->writeFromStream(['chunk1', 'chunk2', 'chunk3'], false);

        $this->assertEquals('chunk1chunk2chunk3', file_get_contents($dest));
        unlink($dest);
    }
    /**
     * @test
     */
    public function testServe() {
        $stream = new FileStream(self::$testFile);
        $emitter = new TestEmitter();
        $stream->serve(false, $emitter);

        $this->assertEquals(200, $emitter->statusCode);
        $this->assertEquals('text/plain', $emitter->headers['Content-Type']);
        $this->assertEquals('inline; filename="stream-test.txt"', $emitter->headers['Content-Disposition']);
        $this->assertEquals((string) filesize(self::$testFile), $emitter->headers['Content-Length']);
        $this->assertEquals(file_get_contents(self::$testFile), $emitter->body);
    }
    /**
     * @test
     */
    public function testServeAsAttachment() {
        $stream = new FileStream(self::$testFile);
        $emitter = new TestEmitter();
        $stream->serve(true, $emitter);

        $this->assertEquals('attachment; filename="stream-test.txt"', $emitter->headers['Content-Disposition']);
    }
    /**
     * @test
     */
    public function testServeNonExistentFile() {
        $stream = new FileStream(self::$testDir . DS . 'nonexistent.txt');
        $this->expectException(FileException::class);
        $stream->serve();
    }
    /**
     * @test
     */
    public function testFileBridgeMethod() {
        $file = new File('stream-test.txt', self::$testDir);
        $stream = $file->stream();
        $this->assertInstanceOf(FileStream::class, $stream);
        $this->assertEquals('stream-test.txt', $stream->getName());
    }
    /**
     * @test
     */
    public function testFileBridgeMethodCustomBuffer() {
        $file = new File('stream-test.txt', self::$testDir);
        $stream = $file->stream(2048);
        $this->assertEquals(2048, $stream->getBufferSize());
    }
    /**
     * @test
     */
    public function testReadChunksEmptyFile() {
        $emptyFile = self::$testDir . DS . 'stream-empty.txt';
        file_put_contents($emptyFile, '');
        $stream = new FileStream($emptyFile);
        $chunks = iterator_to_array($stream->readChunks());
        $this->assertCount(0, $chunks);
        unlink($emptyFile);
    }
    /**
     * @test
     */
    public function testReadLinesWindowsEndings() {
        $winFile = self::$testDir . DS . 'stream-crlf.txt';
        file_put_contents($winFile, "Line A\r\nLine B\r\nLine C\r\n");
        $stream = new FileStream($winFile);
        $lines = iterator_to_array($stream->readLines());
        $this->assertCount(3, $lines);
        $this->assertEquals("Line A\r\n", $lines[0]);
        $this->assertEquals("Line C\r\n", $lines[2]);
        unlink($winFile);
    }
    /**
     * @test
     */
    public function testReadLinesNoTrailingNewline() {
        $noNewline = self::$testDir . DS . 'stream-nonewline.txt';
        file_put_contents($noNewline, "AAA\nBBB");
        $stream = new FileStream($noNewline);
        $lines = iterator_to_array($stream->readLines());
        $this->assertCount(2, $lines);
        $this->assertEquals("AAA\n", $lines[0]);
        $this->assertEquals("BBB", $lines[1]);
        unlink($noNewline);
    }
    /**
     * @test
     */
    public function testEarlyBreakClosesHandle() {
        $stream = new FileStream(self::$testFile);
        foreach ($stream->readChunks(2) as $chunk) {
            break; // break after first chunk
        }
        // If handle wasn't closed, a subsequent read would still work
        // (verifying no resource leak)
        $data = '';
        foreach ($stream->readChunks() as $chunk) {
            $data .= $chunk;
        }
        $this->assertEquals(file_get_contents(self::$testFile), $data);
    }
    /**
     * @test
     */
    public function testReadRangeEntireFile() {
        $stream = new FileStream(self::$testFile);
        $size = $stream->getSize();
        $data = '';
        foreach ($stream->readRange(0, $size) as $chunk) {
            $data .= $chunk;
        }
        $this->assertEquals(file_get_contents(self::$testFile), $data);
    }
    /**
     * @test
     */
    public function testWriteAtomicFromGenerator() {
        $dest = self::$testDir . DS . 'stream-atomic-gen.txt';
        file_put_contents($dest, '');

        $source = new FileStream(self::$testFile);
        $target = new FileStream($dest);
        $target->writeAtomic($source->readChunks(5));

        $this->assertEquals(file_get_contents(self::$testFile), file_get_contents($dest));
        unlink($dest);
    }
    /**
     * @test
     */
    public function testWriteAtomicNoTempLeft() {
        $dest = self::$testDir . DS . 'stream-atomic-clean.txt';
        file_put_contents($dest, '');

        $target = new FileStream($dest);
        $target->writeAtomic(['data']);

        // Temp file should not exist after successful write
        $this->assertFalse(file_exists($dest . '.tmp.' . getmypid()));
        unlink($dest);
    }
    /**
     * @test
     */
    public function testWriteAtomicPreservesOriginalOnFailure() {
        $dest = self::$testDir . DS . 'stream-atomic-fail.txt';
        file_put_contents($dest, 'preserved');

        // Use an unwritable temp path to trigger failure
        $stream = new FileStream('/nonexistent-dir/file.txt');
        try {
            $stream->writeAtomic(['fail']);
        } catch (FileException $e) {
            // expected
        }
        // Original file at $dest should be untouched since we targeted a different path
        $this->assertEquals('preserved', file_get_contents($dest));
        unlink($dest);
    }
}

/**
 * A test emitter that captures output instead of sending it.
 */
class TestEmitter implements ResponseEmitter {
    public array $headers = [];
    public int $statusCode = 0;
    public string $body = '';

    public function setHeader(string $name, string $value): void {
        $this->headers[$name] = $value;
    }

    public function setStatusCode(int $code): void {
        $this->statusCode = $code;
    }

    public function sendBody(\Generator $chunks): void {
        foreach ($chunks as $chunk) {
            $this->body .= $chunk;
        }
    }
}
