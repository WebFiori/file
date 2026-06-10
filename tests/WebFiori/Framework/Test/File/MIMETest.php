<?php

namespace WebFiori\Framework\Test\File;

use PHPUnit\Framework\TestCase;
use WebFiori\File\File;
use WebFiori\File\MIME;

class MIMETest extends TestCase {
    /**
     * @test
     */
    public function testCommonExtensions() {
        $this->assertEquals('image/jpeg', MIME::getType('jpg'));
        $this->assertEquals('image/png', MIME::getType('png'));
        $this->assertEquals('text/plain', MIME::getType('txt'));
        $this->assertEquals('text/html', MIME::getType('html'));
        $this->assertEquals('audio/mpeg', MIME::getType('mp3'));
        $this->assertEquals('video/mp4', MIME::getType('mp4'));
        $this->assertEquals('application/zip', MIME::getType('zip'));
        $this->assertEquals('application/pdf', MIME::getType('pdf'));
    }
    /**
     * @test
     */
    public function testCaseInsensitive() {
        $this->assertEquals('image/jpeg', MIME::getType('JPG'));
        $this->assertEquals('image/png', MIME::getType('Png'));
        $this->assertEquals('text/plain', MIME::getType('TXT'));
        $this->assertEquals('text/html', MIME::getType('HTML'));
    }
    /**
     * @test
     */
    public function testLeadingDot() {
        $this->assertEquals('image/jpeg', MIME::getType('.jpg'));
        $this->assertEquals('image/png', MIME::getType('.png'));
        $this->assertEquals('application/pdf', MIME::getType('.pdf'));
    }
    /**
     * @test
     */
    public function testUnknownExtension() {
        $this->assertEquals(File::DEFAULT_MIME, MIME::getType('xyz123'));
        $this->assertEquals(File::DEFAULT_MIME, MIME::getType('unknownext'));
    }
    /**
     * @test
     */
    public function testEmptyString() {
        $this->assertEquals(File::DEFAULT_MIME, MIME::getType(''));
    }
    /**
     * @test
     */
    public function testWhitespace() {
        // Spaces are not trimmed — treated as part of the extension
        $this->assertEquals(File::DEFAULT_MIME, MIME::getType(' jpg '));
        $this->assertEquals(File::DEFAULT_MIME, MIME::getType('   '));
        // Tabs and newlines are trimmed
        $this->assertEquals('image/png', MIME::getType("\tpng\n"));
    }
}
