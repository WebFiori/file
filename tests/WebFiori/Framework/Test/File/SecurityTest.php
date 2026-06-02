<?php

namespace WebFiori\Framework\Test\File;

use PHPUnit\Framework\TestCase;
use WebFiori\File\Exceptions\FileException;
use WebFiori\File\File;
use WebFiori\File\FileUploader;

/**
 * Security-focused tests for path traversal, permissions, filename sanitization,
 * and range validation.
 */
class SecurityTest extends TestCase {
    /**
     * @test
     */
    public function testSanitizeFilenamePathTraversal() {
        $this->assertEquals('evil.php', FileUploader::sanitizeFilename('../../etc/evil.php'));
        $this->assertEquals('evil.php', FileUploader::sanitizeFilename('..\\..\\windows\\evil.php'));
        $this->assertEquals('evil.php', FileUploader::sanitizeFilename('../evil.php'));
    }
    /**
     * @test
     */
    public function testSanitizeFilenameNullBytes() {
        $this->assertEquals('test.php', FileUploader::sanitizeFilename("test\0.php"));
        $this->assertEquals('test.php', FileUploader::sanitizeFilename("\0test.php"));
    }
    /**
     * @test
     */
    public function testSanitizeFilenameSpecialChars() {
        $this->assertEquals('hello_world_.txt', FileUploader::sanitizeFilename('hello<world>.txt'));
        $this->assertEquals('file_name_.txt', FileUploader::sanitizeFilename('file|name?.txt'));
        $this->assertEquals('normal-file.txt', FileUploader::sanitizeFilename('normal-file.txt'));
        $this->assertEquals('file with spaces.txt', FileUploader::sanitizeFilename('file with spaces.txt'));
    }
    /**
     * @test
     */
    public function testSanitizeFilenameHiddenFiles() {
        $this->assertEquals('htaccess', FileUploader::sanitizeFilename('.htaccess'));
        $this->assertEquals('gitignore', FileUploader::sanitizeFilename('.gitignore'));
    }
    /**
     * @test
     */
    public function testSanitizeFilenamePreservesExtension() {
        $this->assertEquals('document.pdf', FileUploader::sanitizeFilename('document.pdf'));
        $this->assertEquals('image.tar.gz', FileUploader::sanitizeFilename('image.tar.gz'));
    }
    /**
     * @test
     */
    public function testDirectoryPermissionsDefault() {
        $testDir = ROOT_PATH . DS . 'tests' . DS . 'tmp' . DS . 'perm_test_' . getmypid();
        $this->assertFalse(is_dir($testDir));
        File::isDirectory($testDir, true);
        $this->assertTrue(is_dir($testDir));
        // Default should be 0755
        $perms = fileperms($testDir) & 0777;
        $this->assertEquals(0755, $perms);
        rmdir($testDir);
    }
    /**
     * @test
     */
    public function testDirectoryPermissionsCustom() {
        $testDir = ROOT_PATH . DS . 'tests' . DS . 'tmp' . DS . 'perm_test2_' . getmypid();
        File::isDirectory($testDir, true, 0700);
        $this->assertTrue(is_dir($testDir));
        $perms = fileperms($testDir) & 0777;
        $this->assertEquals(0700, $perms);
        rmdir($testDir);
    }
    /**
     * @test
     */
    public function testReadRangeValidationNegativeFrom() {
        $file = new File('text-file.txt', ROOT_PATH . DS . 'tests' . DS . 'files');
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Range values must be >= 0 (or -1 for default).');
        $file->read(-5, 10);
    }
    /**
     * @test
     */
    public function testReadRangeValidationNegativeTo() {
        $file = new File('text-file.txt', ROOT_PATH . DS . 'tests' . DS . 'files');
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Range values must be >= 0 (or -1 for default).');
        $file->read(0, -5);
    }
    /**
     * @test
     */
    public function testReadRangeValidationFromGreaterThanTo() {
        $file = new File('text-file.txt', ROOT_PATH . DS . 'tests' . DS . 'files');
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Range start must be less than range end.');
        $file->read(10, 5);
    }
    /**
     * @test
     */
    public function testReadRangeValidationFromEqualsTo() {
        $file = new File('text-file.txt', ROOT_PATH . DS . 'tests' . DS . 'files');
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Range start must be less than range end.');
        $file->read(5, 5);
    }
    /**
     * @test
     */
    public function testReadRangeValidDefaultSentinelsStillWork() {
        $file = new File('text-file.txt', ROOT_PATH . DS . 'tests' . DS . 'files');
        // -1, -1 should still work (read entire file)
        $file->read(-1, -1);
        $this->assertEquals("Testing the class 'File'.", $file->getRawData());
    }
    /**
     * @test
     */
    public function testReadRangeValidFromWithDefaultTo() {
        $file = new File('text-file.txt', ROOT_PATH . DS . 'tests' . DS . 'files');
        // from=0, to=-1 should read entire file
        $file->read(0, -1);
        $this->assertEquals("Testing the class 'File'.", $file->getRawData());
    }
}
