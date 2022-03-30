<?php

namespace Dodocanfly\SolidEdgeConverter;

use Dodocanfly\SolidEdgeConverter\Contracts\FilesystemInterface;
use Dodocanfly\SolidEdgeConverter\Contracts\ProcessInterface;
use Dodocanfly\SolidEdgeConverter\Exceptions\FileWritePermissionDeniedExtension;
use Dodocanfly\SolidEdgeConverter\Exceptions\InputFileNotExistsExtension;
use Dodocanfly\SolidEdgeConverter\Exceptions\SolidEdgeTranslationServicesException;
use Dodocanfly\SolidEdgeConverter\Exceptions\WrongInputFiletypeException;
use Dodocanfly\SolidEdgeConverter\Exceptions\WrongOutputFiletypeException;
use Dodocanfly\SolidEdgeConverter\Exceptions\WrongSolidEdgeTranslationServicesPathException;
use ReflectionClass;

# To generate 3D PDF files you must enable 3D PDF in:
# C:\Program Files\Siemens\Solid Edge 2022\Program\Define_SolidEdge_Properties_ForWorkflow_ToSync.ini
# set the following line to 1:
# Export 3D PDF from Part or Assembly=1

/**
 * dft => igs, pdf, dwg, dxf,   bmp, jpg, tif, emf
 * psm => x_b/x_t, jt, xgl, sat, model, catpart, ifc, iges/igs, step/stp, 3mf, obj, fbx, stl, plmxml, pdf, u3d, sev, bip, qsm,   bmp, jpg, tif, wrl, bip
 */

class SolidEdgeConverter
{

    public const IMAGE_QUALITY_LOW = 'LOW';
    public const IMAGE_QUALITY_MEDIUM = 'MEDIUM';
    public const IMAGE_QUALITY_HIGH = 'HIGH';

    public const COLOR_DEPTH_MONOCHROME = 1;
    public const COLOR_DEPTH_8_BIT = 8;
    public const COLOR_DEPTH_24_BIT = 24;

    private const AVAILABLE_INPUT_FILE_TYPES = [
        'dft'
    ];
    private const AVAILABLE_OUTPUT_FILE_TYPES = [
        'jpg', 'pdf'
    ];


    private FilesystemInterface $filesystem;
    private ProcessInterface $process;

    private string $solidEdgeTranslationServicesPath = '';

    private string $inputFilePath = '';
    private string $outputFilePath = '';
    private string $outputFormat = '';

    private string $quality = '';
    private int $width = 0;
    private int $height = 0;
    private int $resolution = 0;
    private int $colorDepth = 0;
    private string $multipleSheet = 'FALSE';
    private string $solidEdgeVisible = 'FALSE';

    private array $command = [];

    private array $defaultValues = [];

    private array $errors = [];


    public function __construct(FilesystemInterface $filesystem, ProcessInterface $process)
    {
        $this->filesystem = $filesystem;
        $this->process = $process;
    }

    public function setSolidEdgeTranslationServicesPath(string $solidEdgeTranslationServicesPath): self
    {
        if (
            !$this->filesystem::isFileExists($solidEdgeTranslationServicesPath)
            || $this->filesystem::getExtension($solidEdgeTranslationServicesPath) !== 'exe'
        ) {
            throw new WrongSolidEdgeTranslationServicesPathException(
                'Wrong SolidEdgeTranslationServices path: "' . $solidEdgeTranslationServicesPath . '"'
            );
        }
        $this->solidEdgeTranslationServicesPath = $solidEdgeTranslationServicesPath;
        return $this;
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

    private function resolution(int $resolution): self
    {
        $this->resolution = $resolution;
        return $this;
    }

    public function setResolution100(): self
    {
        return $this->resolution(100);
    }

    public function setResolution200(): self
    {
        return $this->resolution(200);
    }

    public function setResolution300(): self
    {
        return $this->resolution(300);
    }

    public function setResolution600(): self
    {
        return $this->resolution(600);
    }

    public function setResolution1200(): self
    {
        return $this->resolution(1200);
    }

    private function quality(string $quality): self
    {
        $this->quality = $quality;
        return $this;
    }

    public function setQualityLow(): self
    {
        return $this->quality(self::IMAGE_QUALITY_LOW);
    }

    public function setQualityMedium(): self
    {
        return $this->quality(self::IMAGE_QUALITY_MEDIUM);
    }

    public function setQualityHigh(): self
    {
        return $this->quality(self::IMAGE_QUALITY_HIGH);
    }

    private function colorDepth(int $colorDepth): self
    {
        $this->colorDepth = $colorDepth;
        return $this;
    }

    public function setColorDepthMonochrome(): self
    {
        return $this->colorDepth(self::COLOR_DEPTH_MONOCHROME);
    }

    public function setColorDepth8bit(): self
    {
        return $this->colorDepth(self::COLOR_DEPTH_8_BIT);
    }

    public function setColorDepth24bit(): self
    {
        return $this->colorDepth(self::COLOR_DEPTH_24_BIT);
    }

    public function solidEdgeVisible(): self
    {
        $this->solidEdgeVisible = 'TRUE';
        return $this;
    }

    public function solidEdgeInvisible(): self
    {
        $this->solidEdgeVisible = 'FALSE';
        return $this;
    }

    public function multipleSheet(): self
    {
        $this->multipleSheet = 'TRUE';
        return $this;
    }

    public function singleSheet(): self
    {
        $this->multipleSheet = 'FALSE';
        return $this;
    }

    public function convert(): bool
    {
        if (!$this->filesystem::isFileExists($this->solidEdgeTranslationServicesPath)) {
            throw new WrongSolidEdgeTranslationServicesPathException(
                'Wrong SolidEdgeTranslationServices path: "' . $this->solidEdgeTranslationServicesPath . '"'
            );
        }
        $this->prepareParameters();
        $this->process->setCommand($this->command)->run();

        if ($this->isSuccessful()) {
            return true;
        } else {
            print_r($this->errors);
        }
        return false;
    }

    private function analyzeOutput(array $output): void
    {
        $this->clearErrors();
        foreach ($output as $line) {
            if ($this->isError($line)) {
                $this->addError($line);
                throw new SolidEdgeTranslationServicesException($line);
            }
        }
    }

    private function isError(string $outputLine): bool
    {
        return stristr($outputLine, 'error');
    }

    private function clearErrors(): void
    {
        $this->errors = [];
    }

    private function addError(string $error)
    {
        $this->errors[] = iconv('CP1250', 'UTF-8', $error);;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function isSuccessful(): bool
    {
        $this->analyzeOutput($this->process->getOutput());
        return empty($this->errors);
    }


    private function prepareParameters(): void
    {
        $this->command[] = '"' . $this->solidEdgeTranslationServicesPath . '"';
        $this->command[] = '-i="' . $this->inputFilePath . '"';
        $this->command[] = '-o="' . $this->outputFilePath . '"';
        $this->command[] = '-t=' . $this->outputFormat;
        $this->addParameterToCommandArray('v', 'solidEdgeVisible');
        $this->addParameterToCommandArray('w', 'width');
        $this->addParameterToCommandArray('h', 'height');
        $this->addParameterToCommandArray('r', 'resolution');
        $this->addParameterToCommandArray('c', 'colorDepth');
        $this->addParameterToCommandArray('q', 'quality');
        $this->addParameterToCommandArray('m', 'multipleSheet');
    }


    private function addParameterToCommandArray(string $parameterLetter, string $propertyName): void
    {
        if (empty($this->defaultValues)) {
            $reflection = new ReflectionClass(self::class);
            $this->defaultValues = $reflection->getDefaultProperties();
        }
        try {
            if (array_key_exists($propertyName, $this->defaultValues) && $this->defaultValues[$propertyName] !== $this->$propertyName) {
                $this->command[] = '-'.$parameterLetter.'=' . $this->$propertyName;
            }
        } catch (\ReflectionException $e) {
            // do nothing
        }
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
