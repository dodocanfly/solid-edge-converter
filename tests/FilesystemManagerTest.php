<?php


use Dodocanfly\SolidEdgeConverter\FilesystemManager;
use PHPUnit\Framework\TestCase;

class FilesystemManagerTest extends TestCase
{
    public function testIsFileExistsMethod()
    {
        $this->assertTrue(FilesystemManager::isFileExists(__FILE__));
        $this->assertFalse(FilesystemManager::isFileExists('any/non/existent/file.txt'));
    }

    public function testIsDirExistsMethod()
    {
        $this->assertTrue(FilesystemManager::isDirExists(__FILE__));
        $this->assertFalse(FilesystemManager::isDirExists('any/non/existent/directory/file.txt'));
    }

    public function testIsReadableMethod()
    {
        $this->assertFileIsReadable(__DIR__ . '/sample_files/sample_txt_file.txt');
        $this->assertTrue(FilesystemManager::isReadable(__DIR__ . '/sample_files/sample_txt_file.txt'));
    }

    public function testIsWritableMethod()
    {
        $this->assertFileIsWritable(__DIR__ . '/sample_files/sample_txt_file.txt');
        $this->assertTrue(FilesystemManager::isWritable(__DIR__ . '/sample_files/sample_txt_file.txt'));
    }

    public function testIsWritableAnyDirMethod()
    {
        $this->assertFalse(FilesystemManager::isWritableAnyDir('any/non/existent/directory/file.txt'));
        $this->assertFalse(FilesystemManager::isWritableAnyDir('/home/dome'));
        $this->assertTrue(FilesystemManager::isWritable(__DIR__ . '/sample_files/sample_txt_file.txt'));
    }

    public function testGetExtensionMethod()
    {
        self::assertNull(FilesystemManager::getExtension(__DIR__));
        $this->assertEquals('txt', FilesystemManager::getExtension(__DIR__ . '/sample_files/sample_txt_file.txt'));
    }
}
