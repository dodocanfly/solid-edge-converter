<?php

namespace Dodocanfly\SolidEdgeConverter\Contracts;

interface FilesystemInterface
{
    public static function isFileExists(string $path): bool;
    public static function isDirExists(string $path): bool;
    public static function isReadable(string $path): bool;
    public static function isWritable(string $path): bool;
    public static function getExtension(string $path): ?string;

    /**
     * Checks is any dir of path exists and is writable (starting from file to root, except root dir)
     *
     * E.g. if dir /home/user/workspace exists, and you pass
     * /home/user/workspace/project/file.txt it's return true
     * but if you pass /non/existent/directory/and/file.txt it's return false,
     * because method must return false if at end is root
     *
     * @param string $path
     * @return bool
     */
    public static function isWritableAnyDir(string $path): bool;
}
