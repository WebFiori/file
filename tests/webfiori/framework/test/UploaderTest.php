<?php

namespace webfiori\framework\test;

use webfiori\file\Uploader;
use PHPUnit\Framework\TestCase;
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
        $this->assertEquals(__DIR__, $u->getUploadDir());
    }
}
