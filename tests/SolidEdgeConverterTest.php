<?php


use Dodocanfly\SolidEdgeConverter\Exceptions\FileWritePermissionDeniedExtension;
use Dodocanfly\SolidEdgeConverter\Exceptions\InputFileNotExistsExtension;
use Dodocanfly\SolidEdgeConverter\Exceptions\WrongInputFiletypeException;
use Dodocanfly\SolidEdgeConverter\Exceptions\WrongOutputFiletypeException;
use Dodocanfly\SolidEdgeConverter\FilesystemManager;
use Dodocanfly\SolidEdgeConverter\ProcessManager;
use Dodocanfly\SolidEdgeConverter\SolidEdgeConverter;
use PHPUnit\Framework\TestCase;

class SolidEdgeConverterTest extends TestCase
{
    private static function newConverter(): SolidEdgeConverter
    {
        return new SolidEdgeConverter(
            new FilesystemManager(),
            new ProcessManager()
        );
    }


    public function testShouldMakesSolidEdgeConverterObject()
    {
        $converter = self::newConverter();
        self::assertInstanceOf(SolidEdgeConverter::class, $converter);
    }

    public function testConverterGivesNonexistentFile()
    {
        $converter = self::newConverter();
        $this->expectException(InputFileNotExistsExtension::class);
        $converter->from('any/non/existent/file.txt');
    }

    public function testConverterGivesWrongInputFiletype()
    {
        $converter = self::newConverter();
        $this->expectException(WrongInputFiletypeException::class);
        $converter->from(__DIR__ . '/sample_files/sample_txt_file.txt');
    }

    public function testConverterGivesAllowedInputFiletype()
    {
        $converter = self::newConverter();
        $converter = $converter->from(__DIR__ . '/sample_files/sample_dft_file.dft');
        $this->assertInstanceOf(SolidEdgeConverter::class, $converter);
    }

    public function testConverterGivesNullOutputPath()
    {
        $converter = self::newConverter();
        $this->expectException(FileWritePermissionDeniedExtension::class);
        $converter->to('');
    }

    public function testConverterGivesOutputPathWithoutAnyWritableDirectory()
    {
        $converter = self::newConverter();
        $this->expectException(FileWritePermissionDeniedExtension::class);
        $converter->to('/any/non/existent/file.txt');
    }

    public function testConverterGivesOutputPathWithWritableDirectory()
    {
        $converter = self::newConverter();
        $converter = $converter->to(__DIR__ . '/another/directory/file.pdf');
        $this->assertInstanceOf(SolidEdgeConverter::class, $converter);
    }

    public function testConverterGivesOutputPathWithWritableDirectoryButNotAllowedFiletype()
    {
        $converter = self::newConverter();
        $this->expectException(WrongOutputFiletypeException::class);
        $converter->to(__DIR__ . '/another/directory/file.txt');
    }

    public function testConverter()
    {
        $converter = self::newConverter();

        $converter = $converter->from(__DIR__ . '/sample_files/sample_dft_file.dft')
            ->to(__DIR__ . '/another/directory/file.jpg')
            ->depth($converter::COLOR_DEPTH_TRUE_COLOR)
            ->quality($converter::IMAGE_QUALITY_HIGH)
            ->multipleSheet()
            ->solidEdgeVisible()
            ->convert();

        self::assertTrue($converter);
    }

}
