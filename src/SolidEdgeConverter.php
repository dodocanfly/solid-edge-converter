<?php

namespace Dodocanfly\SolidEdgeConverter;

use Dodocanfly\SolidEdgeConverter\Contracts\FilesystemInterface;
use Dodocanfly\SolidEdgeConverter\Contracts\ProcessInterface;
use Dodocanfly\SolidEdgeConverter\Exceptions\FileWritePermissionDeniedExtension;
use Dodocanfly\SolidEdgeConverter\Exceptions\InputFileNotExistsExtension;
use Dodocanfly\SolidEdgeConverter\Exceptions\WrongInputFiletypeException;
use Dodocanfly\SolidEdgeConverter\Exceptions\WrongOutputFiletypeException;

class SolidEdgeConverter
{

    public const IMAGE_QUALITY_LOW = 'LOW';
    public const IMAGE_QUALITY_MEDIUM = 'MEDIUM';
    public const IMAGE_QUALITY_HIGH = 'HIGH';

    public const COLOR_DEPTH_MONOCHROME = 1;
    public const COLOR_DEPTH_256_COLORS = 8;
    public const COLOR_DEPTH_TRUE_COLOR = 24;

    private const AVAILABLE_QUALITIES = [
        self::IMAGE_QUALITY_LOW,
        self::IMAGE_QUALITY_MEDIUM,
        self::IMAGE_QUALITY_HIGH,
    ];
    private const AVAILABLE_COLOR_DEPTHS = [
        self::COLOR_DEPTH_MONOCHROME,
        self::COLOR_DEPTH_256_COLORS,
        self::COLOR_DEPTH_TRUE_COLOR,
    ];
    private const AVAILABLE_RESOLUTIONS = [
        100, 200, 300, 600, 1200
    ];
    private const AVAILABLE_INPUT_FILE_TYPES = [
        'dft'
    ];
    private const AVAILABLE_OUTPUT_FILE_TYPES = [
        'jpg', 'pdf'
    ];

    private const FILETYPE_PARAMETERS = [
        'jpg' => [],
    ];


    private FilesystemInterface $filesystem;
    private ProcessInterface $process;

    private string $inputFilePath = '';
    private string $outputFilePath = '';
    private string $outputFormat = '';

    private string $quality = '';
    private int $width = 0;
    private int $height = 0;
    private int $resolution = 0;
    private int $colorDepth = 0;
    private string $multipleSheet = '';


    public function __construct(FilesystemInterface $filesystem, ProcessInterface $process)
    {
        $this->filesystem = $filesystem;
        $this->process = $process;
    }


    public function from(string $inputFilePath): self
    {
        if (!$this->filesystem::isFileExists($inputFilePath)) {
            throw new InputFileNotExistsExtension('Path: ' . $inputFilePath);
        }
        if (!$this->isInputFileTypeAllowed($inputFilePath)) {
            $allowedExtensions = implode(', ', self::AVAILABLE_INPUT_FILE_TYPES);
            throw new WrongInputFiletypeException('Input file must be one of the following types: ' . $allowedExtensions);
        }
        $this->inputFilePath = $inputFilePath;
        return $this;
    }


    public function to(string $outputFilePath): self
    {
        if (!$this->filesystem::isWritableAnyDir($outputFilePath)) {
            throw new FileWritePermissionDeniedExtension('Path: ' . $outputFilePath);
        }
        if (!$this->isOutputFileTypeAllowed($outputFilePath)) {
            $allowedExtensions = implode(', ', self::AVAILABLE_OUTPUT_FILE_TYPES);
            throw new WrongOutputFiletypeException('Output file must be one of the following types: ' . $allowedExtensions);
        }
        $this->outputFilePath = $outputFilePath;
        $this->outputFormat = $this->filesystem::getExtension($outputFilePath);
        return $this;
    }


    public function width(int $width): self
    {
        $this->width = $width;
        return $this;
    }


    public function height(int $height): self
    {
        $this->height = $height;
        return $this;
    }


    public function resolution(int $resolution): self
    {
        $this->resolution = $resolution;
        return $this;
    }


    public function quality(string $quality): self
    {
        $this->quality = $quality;
        return $this;
    }


    public function depth(int $depth): self
    {
        $this->colorDepth = $depth;
        return $this;
    }


    public function convert(): bool
    {
        $this->prepareParameters();
        $file = $this->inputFilePath . $this->outputFilePath;
        return (bool)$file;
    }


    private function prepareParameters(): void
    {

    }


    private function isFileTypeAllowed(string $path, array $allowedExtensions): bool
    {
        return in_array(
            strtolower($this->filesystem::getExtension($path)),
            $allowedExtensions
        );
    }


    private function isInputFileTypeAllowed(string $path): bool
    {
        return $this->isFileTypeAllowed($path, self::AVAILABLE_INPUT_FILE_TYPES);
    }


    private function isOutputFileTypeAllowed(string $path): bool
    {
        return $this->isFileTypeAllowed($path, self::AVAILABLE_OUTPUT_FILE_TYPES);
    }

}
