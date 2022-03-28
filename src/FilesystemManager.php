<?php

namespace Dodocanfly\SolidEdgeConverter;

use Dodocanfly\SolidEdgeConverter\Contracts\FilesystemInterface;

class FilesystemManager implements FilesystemInterface
{

    public static function isFileExists(string $path): bool
    {
        return file_exists($path);
    }


    public static function isDirExists(string $path): bool
    {
        $dir = self::getFirstDir($path);
        return file_exists($dir);
    }


    public static function isReadable(string $path): bool
    {
        return is_readable($path);
    }


    public static function isWritable(string $path): bool
    {
        return is_writable($path);
    }


    public static function isWritableAnyDir(string $path): bool
    {
        $root = self::getRootDir($path);
        $dir = self::getFirstDir($path);
        while ($dir !== $root) {
            if (is_writable($dir)) return true;
            $dir = dirname($dir);
        }
        return false;
    }


    public static function getExtension(string $path): ?string
    {
        $info = pathinfo($path);
        return array_key_exists('extension', $info) ? $info['extension'] : null;
    }


    private static function getFirstDir(string $path): string
    {
        return is_dir($path) ? $path : dirname($path);
    }


    private static function getRootDir(string $path): string
    {
        return dirname($path, 100);
    }

}
